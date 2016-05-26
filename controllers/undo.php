<?php

require_once 'app/controllers/plugin_controller.php';

class UndoController extends PluginController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem("/profile/delorean");
        $this->internal_limit = 50;

        $deleting = get_config("DELOREAN_MAKE_USERIDS_ANONYMOUS");
        if ($deleting) {
            $old_versions = Sormversion::findBySQL("user_id IS NOT NULL AND mkdate < ?", array(time() - $deleting));
            foreach ($old_versions as $version) {
                $version['user_id'] = null;
                $version->store();
            }
        }
    }

    public function overview_action()
    {
        $this->versions = SormVersion::findBySQL("user_id = ? ORDER BY version_id DESC LIMIT 20", array($GLOBALS['user']->id));
    }

    public function undo_action($id) {
        $this->version = new SormVersion($id);
        if (!Request::isPost() || ($this->version['user_id'] !== $GLOBALS['user']->id)) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $success = $this->version->undo();
        if ($success === "deleted") {
            PageLayout::postMessage(MessageBox::success(_("Änderung wurde rückgängig gemacht, Objekt wurde gelöscht.")));
        }
        if ($success === "nothing") {
            PageLayout::postMessage(MessageBox::info(_("Objekt hätte gelöscht werden müsse, war aber ohnehin nicht mehr da.")));
        }
        if ($success === "changed") {
            PageLayout::postMessage(MessageBox::success(_("Änderung an Objekt rückgängig gemacht.")));
        }
        $this->redirect("undo/overview");
    }



}