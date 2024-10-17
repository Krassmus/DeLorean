<?php

class AddRequestId extends Migration {

    public function up() {
        try {
            DBManager::get()->exec("
                ALTER TABLE sorm_versions
                ADD COLUMN `request_id` VARCHAR(32) DEFAULT NULL AFTER `file_id`
            ");
        } catch(Exception $e) {}
        SimpleORMap::expireTableScheme();
    }

    public function down()
    {

    }

}
