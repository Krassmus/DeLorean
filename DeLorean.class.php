<?php

require_once __DIR__."/classes/SormVersion.class.php";

class DeLorean extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();
        NotificationCenter::addObserver($this, "versioning", "SimpleORMapDidStore");
        NotificationCenter::addObserver($this, "versioning", "SimpleORMapDidDelete");
        if ($GLOBALS['perm']->have_perm("root")) {
            $navigation = new Navigation(_("DeLorean-Wiederherstellung"), PluginEngine::getURL($this, array(), "view/all"));
            Navigation::addItem("/admin/config/delorean", $navigation);
        }
    }

    public function versioning($event, $sorm) {
        if (SormVersion::isAllowed($sorm)) { //very important!
            $version = new SormVersion();
            $version['user_id'] = $GLOBALS['user']->id;
            $version['sorm_class'] = get_class($sorm);
            $version['item_id'] = $sorm->id;
            if ($event === "SimpleORMapDidStore") {
                $version['json_data'] = $sorm->toArray();
            } else {
                $version['json_data'] = null;
            }
            if (is_a($sorm, "StudipDocument")) {
                $version['original_file_path'] = get_upload_file_path($sorm->getId());
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