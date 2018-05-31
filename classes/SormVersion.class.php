<?php

class SormVersion extends SimpleORMap {

    protected $invokation = null;
    static protected $forbidden = array("SormVersion", "PersonalNotifications",
        "Message", "MessageUser", "UserConfigEntry", "MailQueueEntry", "LogEvent");

    static public function getFileDataPath() {
        $folder = $GLOBALS['STUDIP_BASE_PATH'] . "/data/delorean_files";
        if (!file_exists($folder)) {
            mkdir($folder);
        }
        return $folder;
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

    static public function getAllocatedSpace() {
        $statement = DBManager::get()->prepare("
            SELECT
                DATA_LENGTH,
                INDEX_LENGTH
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = :db
                AND TABLE_NAME = 'sorm_versions'
        ");
        $statement->execute(array(
            'db' => $GLOBALS['DB_STUDIP_DATABASE']
        ));
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        $filesize = 0;
        $folder = self::getFileDataPath();
        $files = array_diff(scandir($folder), array('.', '..'));
        foreach ($files as $file) {
            $filesize += filesize($folder . "/" . $file);
        }

        return $data['DATA_LENGTH'] + $data['INDEX_LENGTH'] + $filesize;
    }

    protected static function configure($config = array())
    {
        $config['db_table'] = 'sorm_versions';
        $config['serialized_fields']['json_data'] = 'JSONArrayObject';
        $config['registered_callbacks']['before_store'][]     = 'cbSaveFile';
        $config['registered_callbacks']['before_delete'][]    = 'cbDeleteFile';
        parent::configure($config);
    }

    function cbSaveFile()
    {
        if ($this['original_file_path']) {
            $previous = SormVersion::findOneBySQL("item_id = ? ORDER BY item_id DESC", array(
                $this->json_data['id']
            ));

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

    public function cbDeleteFile() {
        if ($this['original_file_path'] && file_exists($this->getFilePath())) {
            $another_version = SormVersion::countBySQL("version_id != ? AND file_id = ?", array($this->getId(), $this['file_id']));
            if (!$another_version) {
                @unlink($this->getFilePath());
            }
        }
        return true;
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
            if ($this->invokation->isNew()) {
                $this->invokation->setId($this['item_id']);
            }
        }
        return $this->invokation;
    }

    public function undo() {
        if ($this['create']) {
            if ($this->invoke()) {
                $this->invoke()->delete();
                return "deleted";
            } else {
                return "nothing";
            }
        } else { //es gibt eine Vorgängerversion, auf die wir updaten können
            $current = $this->invoke();

            if (!$current) { //es gibt aber keine aktuelle Version mehr. Also bauen wir uns eine.
                $class = $this['sorm_class'];
                $current = new $class();
                if ($this['sorm_class'] == "File") {
                    var_dump("create new File");
                }
            }
            $current->setData($this['json_data']->getArrayCopy());
            $success = $current->store();
            if ($success && $this['original_file_path']) {
                var_dump($this['original_file_path']);
                var_dump($this->getFilePath());
                var_dump(file_exists($this['original_file_path']));
                var_dump(file_exists($this->getFilePath()));
                @copy($this->getFilePath(), $this['original_file_path']);
            }
            return "changed";
        }
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
    public function nextVersion() {
        return SormVersion::findOneBySQL("item_id = :item_id AND version_id > :next_version_id ORDER BY version_id ASC", array(
            'item_id' => $this['item_id'],
            'next_version_id' => $this->getId()
        ));
    }

}