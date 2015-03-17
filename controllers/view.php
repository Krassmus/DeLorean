<?php

require_once 'app/controllers/plugin_controller.php';

class ViewController extends PluginController {

    public function all_action() {
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        Navigation::activateItem("/admin/locations/timetraveller");
        $this->versions = SormVersion::findBySQL("1=1 ORDER BY mkdate DESC");
    }

    public function details_action($id) {
        $this->version = new SormVersion($id);
        if (!$GLOBALS['perm']->have_perm("root") && ($this->version['user_id'] !== $GLOBALS['user']->id)) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        Navigation::activateItem("/admin/locations/timetraveller");
        PageLayout::setTitle(_("Versionsdetails"));
    }

    public function undo_action($id) {
        $this->version = new SormVersion($id);
        if (!$GLOBALS['perm']->have_perm("root") && ($this->version['user_id'] !== $GLOBALS['user']->id)) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $this->previous = $this->version->previousVersion();
        if (!$this->previous) {
            if ($this->version->invoke()) {
                $this->version->invoke()->delete();
                PageLayout::postMessage(MessageBox::success(_("Änderung wurde rückgängig gemacht, Objekt wurde gelöscht.")));
            } else {
                PageLayout::postMessage(MessageBox::info(_("Objekt hätte gelöscht werden müsse, war aber ohnehin nicht mehr da.")));
            }
        } else {
            $this->current = $this->version->invoke();


            if (!$this->current) {
                $class = $this->version['sorm_class'];
                $this->current = new $class();
            }
            $this->current->setData($this->previous['json_data']);
            $success = $this->current->store();
            var_dump($success);
            echo "\n\n<br><br><br>\n\n\n";
            var_dump($this->current);
            die();
            PageLayout::postMessage(MessageBox::success(_("Änderung an Objekt rückgängig gemacht.")));
        }
        $this->redirect("timetraveller/view/all");
    }


}