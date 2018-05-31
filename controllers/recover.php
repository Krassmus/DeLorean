<?php

require_once 'app/controllers/plugin_controller.php';

class RecoverController extends PluginController {

    public function overview_action()
    {
        Navigation::activateItem("/course/files");

        PageLayout::setTitle(_("Wiederherstellung von Ordnern und Dateien"));
        if (!$GLOBALS['perm']->have_studip_perm(Config::get()->DELOREAN_RECOVERY_PERM, Context::get()->id)) {
            throw new AccessDeniedException();
        }
        if (Config::get()->DELOREAN_DELETE_MEMORY) {
            PageLayout::postInfo(sprintf(_("Dateien und Ordner werden maximal %d Tage vorgehalten."), Config::get()->DELOREAN_DELETE_MEMORY));
        }
        $statement = DBManager::get()->prepare("
            SELECT id FROM folders WHERE range_id = ?
        ");
        $statement->execute(array(Context::get()->id));
        $folder_ids = $statement->fetchAll(PDO::FETCH_COLUMN, 0);
        $this->folder_versions = SormVersion::findBySQL("
            `sorm_class` = 'Folder' 
            AND `delete` = '1' 
            AND version_id = (SELECT version_id FROM sorm_versions AS s2 WHERE s2.item_id = sorm_versions.item_id AND s2.sorm_class = sorm_versions.sorm_class ORDER BY version_id DESC LIMIT 1)");
        $this->folder_versions = array_filter($this->folder_versions, function ($version) use ($folder_ids) {
            return ($version['json_data']['range_id'] === Context::get()->id) && (in_array($version['json_data']['parent_id'], $folder_ids));
        }); //only folders that are deleted and could be recovered

        $this->file_versions = SormVersion::findBySQL("
            `sorm_class` = 'FileRef' 
            AND `delete` = '1' 
            AND version_id = (SELECT version_id FROM sorm_versions AS s2 WHERE s2.item_id = sorm_versions.item_id AND s2.sorm_class = sorm_versions.sorm_class ORDER BY s2.version_id DESC LIMIT 1)
        ");
        $this->file_versions = array_filter($this->file_versions, function ($version) use ($folder_ids) {
            return (in_array($version['json_data']['folder_id'], $folder_ids));
        }); //only folders that are deleted and could be recovered
        usort($this->folder_versions, function ($a, $b) {
            return strcasecmp($a['json_data']['name'], $b['json_data']['name']);
        });
        usort($this->file_versions, function ($a, $b) {
            return strcasecmp($a['json_data']['name'], $b['json_data']['name']);
        });
    }

    public function mass_undo_action()
    {
        if (!$GLOBALS['perm']->have_studip_perm(Config::get()->DELOREAN_RECOVERY_PERM, Context::get()->id)) {
            throw new AccessDeniedException();
        }
        foreach (Request::getArray("v") as $version_id) {
            $version = new SormVersion($version_id);
            $this->filefolder_undo($version);
        }
        PageLayout::postMessage(MessageBox::success(_("Objekte wurden wiederhergestellt.")));
        $this->redirect("recover/overview");
    }


    public function undo_action($id)
    {
        if (!$GLOBALS['perm']->have_studip_perm(Config::get()->DELOREAN_RECOVERY_PERM, Context::get()->id)) {
            throw new AccessDeniedException();
        }
        $version = new SormVersion($id);
        if (!Request::isPost()) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $this->filefolder_undo($version);

        PageLayout::postMessage(MessageBox::success(_("Objekt wurde wiederhergestellt.")));
        $this->redirect("recover/overview");
    }

    public function download_file_action($version_id)
    {
        $this->version = new SormVersion($version_id);
        $parentFolder = Folder::find($this->version['json_data']['folder_id']);
        if (!$GLOBALS['perm']->have_studip_perm(Config::get()->DELOREAN_RECOVERY_PERM, $parentFolder['range_id'])) {
            throw new AccessDeniedException();
        }
        $file_id = $this->version['json_data']['file_id'];
        $file = File::find($file_id);
        if (!$file) {
            $file_version = SormVersion::findOneBySQL("`sorm_class` = 'File' AND `item_id` = ? AND `delete` = '1'  AND version_id = (SELECT version_id FROM sorm_versions AS s2 WHERE s2.item_id = sorm_versions.item_id AND s2.sorm_class = sorm_versions.sorm_class ORDER BY version_id DESC LIMIT 1) ", array($file_id));
            if ($file_version) {
                header("Content-Type: ".mime_content_type($file_version->getFilePath()));
                echo file_get_contents($file_version->getFilePath());
                die();
            }
        } else {
            header("Content-Type: ".mime_content_type($file->getPath()));
            echo file_get_contents($file->getPath());
            die();
        }
        throw new Exception("No file.");
    }

    protected function filefolder_undo($version)
    {
        //check if allowed
        if ($version['sorm_class'] === "Folder") {
            if (!$GLOBALS['perm']->have_studip_perm(Config::get()->DELOREAN_RECOVERY_PERM, $version['json_data']['range_id'])) {
                return;
            }
            $version->undo();
            $folder_versions = SormVersion::findBySQL("sorm_class = 'Folder' AND json_data LIKE ?  AND `delete` = '1'  AND version_id = (SELECT version_id FROM sorm_versions AS s2 WHERE s2.item_id = sorm_versions.item_id AND s2.sorm_class = sorm_versions.sorm_class ORDER BY version_id DESC LIMIT 1) ", array("%".$version['item_id']."%"));
            foreach ($folder_versions as $fv) {
                if ($fv['json_data']['parent_id'] === $version['item_id']) {
                    $this->filefolder_undo($fv);
                }
            }
            $file_versions = SormVersion::findBySQL("sorm_class = 'FileRef' AND json_data LIKE ?  AND `delete` = '1'  AND version_id = (SELECT version_id FROM sorm_versions AS s2 WHERE s2.item_id = sorm_versions.item_id AND s2.sorm_class = sorm_versions.sorm_class ORDER BY version_id DESC LIMIT 1) ", array("%".$version['item_id']."%"));
            foreach ($file_versions as $fv) {
                if ($fv['json_data']['folder_id'] === $version['item_id']) {
                    $this->filefolder_undo($fv);
                }
            }
        } else {
            $parentFolder = Folder::find($version['json_data']['folder_id']);
            if (!$parentFolder || !$GLOBALS['perm']->have_studip_perm(Config::get()->DELOREAN_RECOVERY_PERM, $parentFolder['range_id'])) {
                return;
            }
            //FileRef
            $version->undo();
            $file_id = $version['json_data']['file_id'];
            if (!File::find($file_id)) {
                $file_version = SormVersion::findOneBySQL("`sorm_class` = 'File' AND `item_id` = ? AND `delete` = '1'  AND version_id = (SELECT version_id FROM sorm_versions AS s2 WHERE s2.item_id = sorm_versions.item_id AND s2.sorm_class = sorm_versions.sorm_class ORDER BY version_id DESC LIMIT 1) ", array($file_id));
                if ($file_version) {
                    $file_version->undo();
                }
            }
        }
    }





}