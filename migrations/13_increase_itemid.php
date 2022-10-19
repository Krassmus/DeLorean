<?php

class IncreaseItemid extends Migration {

    public function up() {
        DBManager::get()->exec("
            ALTER TABLE `sorm_versions`
            CHANGE `item_id` `item_id` varchar(98) NOT NULL
        ");
        SimpleORMap::expireTableScheme();
    }

}
