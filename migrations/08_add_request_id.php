<?php

class AddRequestId extends Migration {

    public function up() {
        DBManager::get()->exec("
            ALTER TABLE sorm_versions
            ADD COLUMN `request_id` VARCHAR(32) DEFAULT NULL AFTER `file_id`
        ");
        SimpleORMap::expireTableScheme();
    }

    public function down()
    {

    }

}
