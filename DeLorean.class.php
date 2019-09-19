<?php

require_once __DIR__."/lib/SormVersion.php";
require_once __DIR__."/lib/DatetimeWidget.php";

class DeLorean extends StudIPPlugin implements SystemPlugin {

    public function __construct()
    {
        parent::__construct();
        NotificationCenter::addObserver($this, "versioning", "");
        if ($GLOBALS['perm']->have_perm("root")) {
            $navigation = new Navigation(_("DeLorean-Wiederherstellungsmaschine"), PluginEngine::getURL($this, array(), "view/all"));
            Navigation::addItem("/admin/config/delorean", $navigation);
        }
        if (Context::get()->id
                && $GLOBALS['perm']->have_studip_perm(Config::get()->DELOREAN_RECOVERY_PERM, Context::get()->id)
                && Navigation::hasItem("/course/files")
                && (stripos($_SERVER['REQUEST_URI'], "dispatch.php/course/files") !== false)) {
            NotificationCenter::addObserver($this, "addToFilesSidebar", "SidebarWillRender");
        }

    }

    public function addToFilesSidebar()
    {
        $statement = DBManager::get()->prepare("
            SELECT id FROM folders WHERE range_id = ?
        ");
        $statement->execute(array(Context::get()->id));
        $folder_ids = $statement->fetchAll(PDO::FETCH_COLUMN, 0);
        $versions = SormVersion::findBySQL("(`sorm_class` = 'Folder' OR `sorm_class` = 'FileRef') AND `delete` = '1' AND version_id = (SELECT version_id FROM sorm_versions AS s2 WHERE s2.item_id = sorm_versions.item_id AND s2.sorm_class = sorm_versions.sorm_class ORDER BY version_id DESC LIMIT 1)");
        $versions = array_filter($versions, function ($version) use ($folder_ids) {
            if ($version['sorm_class'] === "Folder") {
                return ($version['json_data']['range_id'] === Context::get()->id) && (in_array($version['json_data']['parent_id'], $folder_ids));
            } else {
                return (in_array($version['json_data']['folder_id'], $folder_ids));
            }
        });
        if (count($versions)) {
            $actions = Sidebar::Get()->getWidget("actions");
            $actions->addLink(
                _("Objekte wiederherstellen"),
                PluginEngine::getURL($this, array(), "recover/overview"),
                Icon::create("archive2", "clickable")
            );
        }
    }

    public function versioning($event, $sorm) {
        if (substr($event, -10) === "WillDelete") {
            $action = "delete";
        }
        if (substr($event, -9) === "DidCreate") {
            $action = "create";
        }
        if (substr($event, -9) === "WillStore") {
            $action = "store";
        }
        if ($action
                && is_a($sorm, "SimpleORMap")
                && ($action !== "store" || !$sorm->isNew()) //So by create AND store we don't get twice the same object
                && ($action !== "store" || $sorm->isDirty()) //And only if something changed. We need to check that because WillStore is earlier than the isDirty check by SORM
                && SormVersion::isAllowed($sorm)) { //Very important!
            $version = new SormVersion();
            if (Config::get()->DELOREAN_MAKE_USERIDS_ANONYMOUS) {
                $version['user_id'] = $GLOBALS['user']->id;
            }
            $version['sorm_class'] = get_class($sorm);
            $version['item_id'] = $sorm->id ?: $sorm->getId();
            $version['json_data'] = $sorm->toRawArray();
            $version['delete'] = 0;
            $version['create'] = 0;
            if ($action === "delete") {
                $version['delete'] = 1;
            }
            if ($action === "create") {
                $version['create'] = 1;
            }
            if (is_a($sorm, "File") && ($sorm['storage'] === "disk")) {
                $path = $sorm->getPath();
                if ($path) {
                    $version['original_file_path'] = $path;
                }
            }
            $version->store();
        }
        return true;
    }

    public function stringToColorCode($str) {
        $code = dechex(crc32($str));
        $code = substr($code, 0, 6);
        return $code;
    }

}