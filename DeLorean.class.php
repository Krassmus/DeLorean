<?php

require_once __DIR__."/classes/SormVersion.class.php";

class DeLorean extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();
        NotificationCenter::addObserver($this, "versioning", "SimpleORMapDidStore");
        if ($GLOBALS['perm']->have_perm("root")) {
            $navigation = new Navigation(_("DeLorean-Wiederherstellung"), PluginEngine::getURL($this, array(), "view/all"));
            Navigation::addItem("/admin/locations/timetraveller", $navigation);
        }
    }

    public function versioning($event, $sorm) {
        if (SormVersion::isAllowed($sorm)) { //very important!
            $version = new SormVersion();
            $version['user_id'] = $GLOBALS['user']->id;
            $version['sorm_class'] = get_class($sorm);
            $version['item_id'] = $sorm->id;
            $version['json_data'] = $sorm->toArray();
            $version->store();
        }
        return true;
    }

}