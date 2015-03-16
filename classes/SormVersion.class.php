<?php

class SormVersion extends SimpleORMap {

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
        $this->content['json_data'] = (array) studip_utf8decode(json_decode($this->content['json_data']));
        $this->content_db['json_data'] = (array) studip_utf8decode(json_decode($this->content_db['json_data']));
        return true;
    }

}