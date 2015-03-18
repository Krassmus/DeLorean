<?php

class SormVersion extends SimpleORMap {

    protected $invokation = null;
    static protected $forbidden = array("SormVersion", "PersonalNotification");

    static public function isAllowed($class) {
        $class = is_object($class) ? get_class($class) : $class;
        return (is_a($class, "SimpleORMap")
            && !in_array($class, self::$forbidden));
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
        return SormVersion::findOneBySQL("item_id = :item_id AND mkdate < :mkdate", array(
            'item_id' => $this['item_id'],
            'mkdate' => $this['mkdate']
        ));
    }

}