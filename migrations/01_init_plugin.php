<?php

class InitPlugin extends Migration {
    
    public function up() {
        DBManager::get()->exec("
            CREATE TABLE `sorm_versions` (
                `version_id` bigint(20) NOT NULL AUTO_INCREMENT,
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
        Config::get()->create("DELOREAN_MAKE_USERIDS_ANONYMOUS", array(
            'value' => 86400 * 30,
            'type' => "integer",
            'range' => "global",
            'section' => "DELOREAN",
            'description' => "After how many seconds should the users in the version-table be anonymized? This is for privacy-concerns."
        ));
    }

    public function down() {
        DBManager::get()->exec("DROP TABLE `sorm_versions` ");
        $folder = $GLOBALS['STUDIP_BASE_PATH'] . "/data/delorean_files";

        $files = array_diff(scandir($folder), array('.', '..'));
        foreach ($files as $file) {
            unlink($folder . "/" . $file);
        }
        rmdir($folder);
        Config::get()->delete("DELOREAN_MAKE_USERIDS_ANONYMOUS");
    }

}