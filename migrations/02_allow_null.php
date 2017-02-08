<?php

class AllowNull extends Migration {
    
    public function up() {
        DBManager::get()->exec("
            ALTER TABLE `sorm_versions` 
            CHANGE `json_data` `json_data` text NULL
        ");
    }

}