<?php

require_once __DIR__."/classes/SormVersion.class.php";

class DeLorean extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();
        NotificationCenter::addObserver($this, "versioning", "SimpleORMapDidStore");
        NotificationCenter::addObserver($this, "versioning", "SimpleORMapDidDelete");
        if ($GLOBALS['perm']->have_perm("root")) {
            $navigation = new Navigation(_("DeLorean-Wiederherstellungsmaschine"), PluginEngine::getURL($this, array(), "view/all"));
            Navigation::addItem("/admin/config/delorean", $navigation);
        }
        if (Navigation::hasItem("/profile") && $GLOBALS['perm']->have_perm("autor") && !get_config("DELOREAN_MAKE_USERIDS_ANONYMOUS")) {
            $nav = new Navigation(_("Rückgängig"), PluginEngine::getURL($this, array(), "undo/overview"));
            Navigation::addItem("/profile/delorean", $nav);
        }
    }

    public function versioning($event, $sorm) {
        if (SormVersion::isAllowed($sorm)) { //very important!
            $version = new SormVersion();
            if (!get_config("DELOREAN_MAKE_USERIDS_ANONYMOUS")) {
                $version['user_id'] = $GLOBALS['user']->id;
            }
            $version['sorm_class'] = get_class($sorm);
            $version['item_id'] = $sorm->id ?: $sorm->getId();
            if ($event === "SimpleORMapDidStore") {
                $version['json_data'] = method_exists($sorm, "toRawArray")
                    ? $sorm->toRawArray()
                    : $sorm->toArray();
            } elseif($event === "SimpleORMapDidDelete") {
                $version['json_data'] = null;
            }
            if (is_a($sorm, "StudipDocument")
                    || (is_a($sorm, "File") && $sorm->getStorageObject())) {
                if (is_a($sorm, "StudipDocument")) {
                    $path = get_upload_file_path($sorm->getId());
                } elseif (is_a($sorm, "File") && $sorm->getStorageObject()) {
                    $path = $sorm->getStoragePath();
                }
                if ($path) {
                    $version['original_file_path'] = $path;
                }
            }
            $version->store();
            //var_dump($version); die();
        }
        return true;
    }

    public function stringToColorCode($str) {
        $code = dechex(crc32($str));
        $code = substr($code, 0, 6);
        return $code;
    }

}