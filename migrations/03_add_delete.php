<?php

require_once __DIR__.'/../cleanup_delorean.cronjob.php';

class AddDelete extends Migration {

    public function up() {
        try {
            DBManager::get()->exec("
                ALTER TABLE `sorm_versions`
                ADD `delete` TINYINT DEFAULT '0' AFTER `file_id`
            ");
        } catch (Exception $e) {}
        try {
            DBManager::get()->exec("
                ALTER TABLE `sorm_versions`
                ADD `create` TINYINT DEFAULT '0' AFTER `file_id`
            ");

            DBManager::get()->exec("
                DELETE FROM `sorm_versions`
            ");
            Config::get()->create("DELOREAN_DELETE_MEMORY", array(
                'value' => 365,
                'type' => "integer",
                'range' => "global",
                'section' => "DELOREAN",
                'description' => "After how many days should the versions be deleted? If this is 0 nothing will get deleted at all. If this is not what you expected, you better should uninstall this plugin."
            ));
            CleanupDelorean::register()->schedulePeriodic()->activate();
        } catch (Exception $e) {}

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        Config::get()->delete("DELOREAN_DELETE_MEMORY");
    }

}
