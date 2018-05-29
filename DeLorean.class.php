<?php

require_once __DIR__."/classes/SormVersion.class.php";

class DeLorean extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();
        NotificationCenter::addObserver($this, "versioning", "");
        if ($GLOBALS['perm']->have_perm("root")) {
            $navigation = new Navigation(_("DeLorean-Wiederherstellungsmaschine"), PluginEngine::getURL($this, array(), "view/all"));
            Navigation::addItem("/admin/config/delorean", $navigation);
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