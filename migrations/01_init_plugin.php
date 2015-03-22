<?php

class InitPlugin extends Migration {
    
    public function up() {
        DBManager::get()->exec("
            CREATE TABLE `sorm_versions` (
                `version_id` varchar(32) NOT NULL,
                `user_id` varchar(32) NULL,
                `sorm_class` varchar(128) NOT NULL,
                `item_id` varchar(97) NOT NULL,
                `json_data` text NOT NULL,
                `original_file_path` varchar(100) NULL,
                `file_id` varchar(100) NULL,
                `mkdate` text NOT NULL,
                PRIMARY KEY (`version_id`),
                KEY `user_id` (`user_id`),
                KEY `sorm_class` (`sorm_class`),
                KEY `item_id` (`item_id`)
            ) ENGINE=InnoDB
        ");
        DBManager::get()->exec("
        INSERT IGNORE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`)
        VALUES
            ('4cdb0c9bbd2d869b71e4a354c4ae2cb1', '', 'DELOREAN_MAKE_USERIDS_ANONYMOUS', 86400 * 30, 0, 'integer', 'global', 'DELOREAN', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'After how many seconds should the users in the version-table be anonymized? This is for privacy-concerns.', '', '')
        ");
    }

    public function down() {
        DBManager::get()->exec("DROP TABLE `sorm_versions` ");
        $folder = $GLOBALS['STUDIP_BASE_PATH'] . "/data/delorean_files";

        $files = array_diff(scandir($folder), array('.', '..'));
        foreach ($files as $file) {
            unlink($folder . "/" . $file);
        }
        rmdir($folder);
    }

}