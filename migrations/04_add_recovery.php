<?php

class AddRecovery extends Migration {
    
    public function up() {

        Config::get()->create("DELOREAN_RECOVERY_PERM", array(
            'value' => "tutor",
            'type' => "string",
            'range' => "global",
            'section' => "DELOREAN",
            'description' => "Which status 'tutor', 'dozent', 'admin', 'root' is able to see the recovery option in the course filesystem?"
        ));


        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        Config::get()->delete("DELOREAN_RECOVERY_PERM");
    }

}