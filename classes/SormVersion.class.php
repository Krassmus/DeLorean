<?php

class SormVersion extends SimpleORMap {

    protected $invokation = null;
    static protected $forbidden = array("SormVersion", "PersonalNotifications",
        "Message", "MessageUser");

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
        $this->registerCallback('after_store', 'cbSaveFile');
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
        if ($this['original_file_path'] && file_exists($this['original_file_path'])) {
            @copy($this['original_file_path'], $this->getFilePath());
        }
        return true;
    }

    public function delete() {
        parent::delete();
        if ($this['original_file_path'] && file_exists($this->getFilePath())) {
            @unlink($this->getFilePath());
        }
    }

    public function getFilePath() {
        if (!file_exists(self::getFileDataPath())) {
            mkdir(self::getFileDataPath());
        }
        if (!$this->getId()) {
            $this->setId($this->getNewId());
        }
        return self::getFileDataPath()."/".$this->getId();
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
        return SormVersion::findOneBySQL("item_id = :item_id AND version_id < :next_version_id", array(
            'item_id' => $this['item_id'],
            'next_version_id' => $this->getId()
        ));
    }

}