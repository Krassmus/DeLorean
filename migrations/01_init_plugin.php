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
            ('962a65b71d64c40d21fc494b1f18c4f9', '', 'DELOREAN_ANONYMOUS_USERS', 0, 0, 'boolean', 'global', 'DELOREAN', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Should DeLorean save the users that have changed the objects (false) or should they be made anonymous (true)?', '', ''),
            ('989b4c3e7bf87dd28c0a6f927b06d595', '', 'DELOREAN_SAVING_TIME', 86400 * 30, 0, 'integer', 'global', 'DELOREAN', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'How many seconds should the data be save until it is automatically deleted? 0 means never deleting anything.', '', '')
        ");
    }

}