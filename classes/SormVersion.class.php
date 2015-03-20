<?php

class SormVersion extends SimpleORMap {

    protected $invokation = null;
    static protected $forbidden = array("SormVersion", "PersonalNotifications",
        "Message", "MessageUser", "UserConfigEntry");

    static public function getFileDataPath() {
        return $GLOBALS['STUDIP_BASE_PATH'] . "/data/delorean_files";
    }

    static public function isAllowed($class) {
        if (!is_a($class, "SimpleORMap")) {
            return false;
        }
        foreach(self::$forbidden as $forbidden_class) {
            if (is_a($class, $forbidden_class)) {
                return false;
            }
        }
        return true;
    }

    protected static function configure($config = array())
    {
        $config['db_table'] = 'sorm_versions';
        parent::configure($config);
    }

    function __construct($id = null)
    {
        $this->registerCallback('before_store', 'cbSerializeData');
        $this->registerCallback('after_store after_initialize', 'cbUnserializeData');
        $this->registerCallback('before_store', 'cbSaveFile');
        parent::__construct($id);
    }

    function cbSerializeData()
    {
        $this->content['json_data'] = json_encode(studip_utf8encode($this->content['json_data']));
        $this->content_db['json_data'] = json_encode(studip_utf8encode($this->content_db['json_data']));
        return true;
    }

    function cbUnserializeData()
    {
        $this->content['json_data'] = (array) studip_utf8decode(json_decode($this->content['json_data'], true));
        $this->content_db['json_data'] = (array) studip_utf8decode(json_decode($this->content_db['json_data'], true));
        return true;
    }

    function cbSaveFile()
    {
        if ($this['original_file_path']) {
            $json_data = (json_decode($this['json_data'], true));
            $previous = SormVersion::findOneBySQL("item_id = ? ORDER BY item_id DESC", array($json_data['id']));

            if ($previous
                    && $previous->getFilePath()
                    && file_exists($previous->getFilePath())
                    && file_exists($this['original_file_path'])
                    && (md5_file($this['original_file_path']) === md5_file($previous->getFilePath()))
                ) {
                $this->content['file_id'] = $previous['file_id'];
            } else {
                $this->content['file_id'] = md5(uniqid());
                @copy($this['original_file_path'], $this->getFilePath());
            }
        }
        return true;
    }

    public function delete() {
        parent::delete();
        if ($this['original_file_path'] && file_exists($this->getFilePath())) {
            $another_version = SormVersion::countBySQL("file_id = ?", array($this['file_id']));
            if (!$another_version) {
                @unlink($this->getFilePath());
            }
        }
    }

    public function getFilePath() {
        if (!file_exists(self::getFileDataPath())) {
            mkdir(self::getFileDataPath());
        }
        if (!$this['file_id']) {
            $this['file_id'] = md5(uniqid());
        }
        return self::getFileDataPath()."/".$this['file_id'];
    }

    public function invoke() {
        if ($this->invokation === null) {
            $class = $this['sorm_class'];
            $this->invokation = new $class($this['item_id']);
        }
        return $this->invokation;
    }

    public function isCurrentObject() {
        if (!isset($this['sorm_class']['chdate'])) {
            return false;
        }
        $class = $this['sorm_class'];
        $new_instance = new $class($this['item_id']);
        return $new_instance->isField("chdate")
            && $new_instance['chdate'] <= $this['sorm_class']['chdate'];
    }

    public function previousVersion() {
        return SormVersion::findOneBySQL("item_id = :item_id AND version_id < :next_version_id ORDER BY version_id DESC", array(
            'item_id' => $this['item_id'],
            'next_version_id' => $this->getId()
        ));
    }

}