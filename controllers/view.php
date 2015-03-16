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


}