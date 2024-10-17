<?php

class AddRequestIndex extends Migration {

    public function up() {
        try {
            DBManager::get()->exec("
                ALTER TABLE `sorm_versions`
                ADD COLUMN `search_index` text DEFAULT NULL AFTER `json_data`,
                ADD INDEX `request_id` (`request_id`),
                ADD FULLTEXT(`search_index`)
            ");
        } catch (Exception $e) {}
    }

    public function down()
    {
        DBManager::get()->exec("
            ALTER TABLE `sorm_versions`
            DROP INDEX `request_id`,
            DROP COLUMN `search_index`
        ");
    }

}
