<?php

require_once 'app/controllers/plugin_controller.php';

class UndoController extends PluginController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem("/profile/delorean");
        $this->internal_limit = 30;

        $deleting = get_config("DELOREAN_MAKE_USERIDS_ANONYMOUS");
        if ($deleting) {
            $statement = DBManager::get()->prepare("
                UPDATE sorm_versions
                SET user_id = null
                WHERE user_id IS NOT NULL
                    AND mkdate < UNIX_TIMESTAMP() - ?
            ");
            $statement->execute(array($deleting));
        }
    }

    public function overview_action()
    {
        $this->versions = SormVersion::findBySQL("user_id = ? ORDER BY version_id DESC LIMIT ".$this->internal_limit, array($GLOBALS['user']->id));
    }

    public function undo_action($id) {
        $this->version = new SormVersion($id);
        if (!Request::isPost() || ($this->version['user_id'] !== $GLOBALS['user']->id)) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $success = $this->version->undo();
        if ($success === "deleted") {
            PageLayout::postMessage(MessageBox::success(_("�nderung wurde r�ckg�ngig gemacht, Objekt wurde gel�scht.")));
        }
        if ($success === "nothing") {
            PageLayout::postMessage(MessageBox::info(_("Objekt h�tte gel�scht werden m�sse, war aber ohnehin nicht mehr da.")));
        }
        if ($success === "changed") {
            PageLayout::postMessage(MessageBox::success(_("�nderung an Objekt r�ckg�ngig gemacht.")));
        }
        $this->redirect("undo/overview");
    }



}