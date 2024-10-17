<?php

class RenamePlugin extends Migration {

    public function up()
    {
        DBManager::get()->exec('
            UPDATE `plugins`
            SET `pluginname` = "DeLorean"
            WHERE `pluginclassname` = "DeLorean"
        ');
        try {
            DBManager::get()->exec('
                UPDATE `schema_version`
                SET `domain` = "DeLorean"
                WHERE `domain` = "DeLorean-Wiederherstellungsmaschine"
            ');
        } catch (Exception $e) {}
        DBManager::get()->exec('
            UPDATE `log_events`
            SET `coaffected_range_id` = "DeLorean"
            WHERE `coaffected_range_id` = "DeLorean-Wiederherstellungsmas"
               OR `coaffected_range_id` = "DeLorean-Wiederherstellungsmaschine"
        ');
    }

    public function down()
    {

    }

}
