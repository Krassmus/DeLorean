<?php

class SormVersion extends SimpleORMap {

    protected $invokation = null;
    static protected $forbidden = array("SormVersion", "PersonalNotifications",
        "Message", "MessageUser", "UserConfigEntry", "MailQueueEntry", "LogEvent");

    static public function cleanDBUp()
    {
        $deleting = Config::get()->DELOREAN_DELETE_MEMORY;
        if ($deleting > 0) {
            $limit = 1000; //delete in chunks so that we don't need too much memory
            do {
                $deleted = SormVersion::deleteBySQL("mkdate < UNIX_TIMESTAMP() - ? LIMIT " . $limit, array($deleting * 86400));
            } while ($deleted == $limit);
        }
        $anonymizing = Config::get()->DELOREAN_MAKE_USERIDS_ANONYMOUS;
        if ($anonymizing > 0) {
            $statement = DBManager::get()->prepare("
                UPDATE sorm_versions
                SET user_id = null
                WHERE user_id IS NOT NULL
                    AND mkdate < UNIX_TIMESTAMP() - ?
            ");
            $statement->execute(array($anonymizing));
        }
        self::removeDatebaseEntries();
        $cache_key = "DeLorean/allocatedDBSpace";
        StudipCacheFactory::getCache()->expire($cache_key);
    }

    protected static function removeDatebaseEntries()
    {
        if (Config::get()->DELOREAN_MAX_SIZE > 0) {
            $old_allocated_space = self::getAllocatedSpace();
            $freeed_space = 0;
            while ($old_allocated_space - $freeed_space > Config::get()->DELOREAN_MAX_SIZE) {
                $last = self::findOneBySQL("1 = 1 ORDER BY version_id ASC LIMIT 1");
                if ($last) {
                    $freeed_space += strlen($last['json_data']) + strlen($last['search_index']) + 20 + 32 + 128 + 97 + 100 + 100 + 4 + 4 + 11;
                    if (file_exists($last->getFilePath())) {
                        $freeed_space += filesize($last->getFilePath());
                    }
                    $last->delete();
                } else {
                    break;
                }
            }
        }
    }

    static public function getFileDataPath()
    {
        $folder = trim(Config::get()->DELOREAN_DATA_PATH) ?: $GLOBALS['STUDIP_BASE_PATH'] . "/data/delorean_files";
        if (!file_exists($folder)) {
            $success = @mkdir($folder);
            if (!$success && $GLOBALS['perm']->have_perm("root")) {
                PageLayout::postError(_("Konnte Verzeichnis data/delorean_files nicht erstellen."));
            }
            if (!$success) {
                $folder = null;
            }
        }
        return $folder;
    }

    static public function isAllowed($class)
    {
        if (!is_a($class, "SimpleORMap")) {
            return false;
        }
        $forbidden = (array) preg_split("/\s+/", Config::get()->DELOREAN_DISABLED_CLASSES, null, PREG_SPLIT_NO_EMPTY);
        array_unshift( $forbidden, "SormVersion");
        foreach ($forbidden as $forbidden_class) {
            if (is_a($class, $forbidden_class)) {
                return false;
            }
        }
        return true;
    }

    static public function getAllocatedSpace()
    {
        return self::getAllocatedDBSpace() + self::getAllocatedFileSpace();
    }

    static public function getAllocatedDBSpace()
    {
        $cache = StudipCacheFactory::getCache();
        $cache_key = "DeLorean/allocatedDBSpace";
        $cached_value = $cache->read($cache_key);
        if ($cached_value) {
            return $cached_value;
        }
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

        $cache->write(
            $cache_key,
            $data['DATA_LENGTH'] + $data['INDEX_LENGTH'],
            15 * 60
        );
        return $data['DATA_LENGTH'] + $data['INDEX_LENGTH'];
    }

    static public function getAllocatedFileSpace()
    {
        $cache = StudipCacheFactory::getCache();
        $cache_key = "DeLorean/allocatedFileSpace";
        $cached_value = $cache->read($cache_key);
        if ($cached_value) {
            return $cached_value;
        }
        $filesize = 0;
        $folder = self::getFileDataPath();
        if ($folder) {
            $handle = opendir($folder);
            while (false !== ($file = readdir($handle))) {
                if (!in_array($file, ['.','..'])) {
                    if (file_exists($folder . "/" . $file)) {
                        $filesize += filesize($folder . "/" . $file);
                    }
                }
            }
        }
        $cache->write($cache_key, $filesize, 15 * 60);
        return $filesize;
    }

    protected static function configure($config = array())
    {
        $config['db_table'] = 'sorm_versions';
        $config['serialized_fields']['json_data'] = 'JSONArrayObject';
        $config['registered_callbacks']['before_store'][]     = 'cbSaveFile';
        $config['registered_callbacks']['before_delete'][]    = 'cbDeleteFile';
        $config['registered_callbacks']['after_store'][]      = 'cbCleanUp';
        parent::configure($config);
    }

    function cbSaveFile()
    {
        if ($this['original_file_path'] && !$this['create']) {
            $previous = SormVersion::findOneBySQL("item_id = ? ORDER BY item_id DESC", array(
                $this->json_data['id']
            ));

            if ($previous
                    && $previous->getFilePath()
                    && file_exists($previous->getFilePath())
                    && file_exists($this['original_file_path'])
                    && (md5_file($this['original_file_path']) === md5_file($previous->getFilePath()))
                ) {
                //Wenn die Dateien sich nicht unterscheiden, übernimm einfach die alte Datei:
                $this->content['file_id'] = $previous['file_id'];
            } else {
                $this->content['file_id'] = md5(uniqid());
                $filePath = $this->getFilePath();
                if ($filePath) {
                    @copy($this['original_file_path'], $filePath);
                    @chmod($filePath,0666);
                }
            }
        }
        return true;
    }

    public function cbDeleteFile()
    {
        $filePath = $this->getFilePath();
        if ($this['original_file_path'] && file_exists($filePath)) {
            $another_version = SormVersion::countBySQL("version_id != ? AND file_id = ?", array($this->getId(), $this['file_id']));
            if (!$another_version) {
                if ($filePath) {
                    @unlink($filePath);
                }
            }
        }
        return true;
    }

    public function cbCleanUp()
    {
        if (Config::get()->DELOREAN_CLEANUP_ALWAYS) {
            self::removeDatebaseEntries();
        }
    }

    public function getFilePath()
    {
        $folder = self::getFileDataPath();
        if (!$folder) {
            return false;
        }
        if (!$this['file_id']) {
            $this['file_id'] = md5(uniqid());
        }
        return self::getFileDataPath()."/".$this['file_id'];
    }

    public function invoke()
    {
        if ($this->invokation === null) {
            $class = $this['sorm_class'];
            $id = $this['item_id'];
            if (strpos($id, "_") !== false) {
                $id = explode("_", $id);
            }

            $this->invokation = new $class($id);
            if ($this->invokation->isNew()) {
                $this->invokation->setId($id);
            }
        }
        return $this->invokation;
    }

    public function undo()
    {
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

            if ($this['sorm_class'] === 'MessageUser') {
                $current->deleted = 0;
            }

            $success = $current->store();
            if ($success && $this['original_file_path']) {
                @copy($this->getFilePath(), $this['original_file_path']);
            }
            return "changed";
        }
    }

    public function isCurrentObject()
    {
        if (!isset($this['sorm_class']['chdate'])) {
            return false;
        }
        $class = $this['sorm_class'];
        $new_instance = new $class($this['item_id']);
        return $new_instance->isField("chdate")
            && $new_instance['chdate'] <= $this['sorm_class']['chdate'];
    }

    public function previousVersion()
    {
        return SormVersion::findOneBySQL("item_id = :item_id AND version_id < :next_version_id ORDER BY version_id DESC", array(
            'item_id' => $this['item_id'],
            'next_version_id' => $this->getId()
        ));
    }

    public function nextVersion()
    {
        return SormVersion::findOneBySQL("item_id = :item_id AND version_id > :next_version_id ORDER BY version_id ASC", array(
            'item_id' => $this['item_id'],
            'next_version_id' => $this->getId()
        ));
    }

}
