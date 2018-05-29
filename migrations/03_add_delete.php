<?php

class AddDelete extends Migration {
    
    public function up() {
        DBManager::get()->exec("
            ALTER TABLE `sorm_versions` 
            ADD `delete` TINYINT DEFAULT '0'
        ");
        DBManager::get()->exec("
            ALTER TABLE `sorm_versions` 
            ADD `create` TINYINT DEFAULT '0'
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
        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        Config::get()->delete("DELOREAN_DELETE_MEMORY");
    }

}