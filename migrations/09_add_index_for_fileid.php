<?php

class AddIndexForFileid extends Migration {

    public function up() {
        DBManager::get()->exec("
            ALTER TABLE sorm_versions
            ADD INDEX `file_id` (`file_id`)
        ");
        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->exec("
            ALTER TABLE sorm_versions
            DROP INDEX `file_id`
        ");
        SimpleORMap::expireTableScheme();
    }

}
