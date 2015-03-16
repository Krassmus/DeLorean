<?php

class InitPluginMigration extends Migration {
    
    public function up() {
        DBManager::get()->exec("
            CREATE TABLE `sorm_versions` (
                `version_id` varchar(32) NOT NULL,
                `user_id` varchar(32) NOT NULL,
                `sorm_class` varchar(128) NOT NULL,
                `item_id` varchar(97) NOT NULL,
                `json_data` text NOT NULL,
                `mkdate` text NOT NULL,
                PRIMARY KEY (`version_id`),
                KEY `user_id` (`user_id`),
                KEY `sorm_class` (`sorm_class`),
                KEY `item_id` (`item_id`)
            ) ENGINE=InnoDB
        ");
    }

}