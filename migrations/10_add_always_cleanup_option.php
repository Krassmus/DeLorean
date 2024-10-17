<?php

class AddAlwaysCleanupOption extends Migration {

    public function up() {
        if (!Config::get()->offsetExists("DELOREAN_CLEANUP_ALWAYS")) {
            Config::get()->create("DELOREAN_CLEANUP_ALWAYS", array(
                'value' => "0",
                'type' => "boolean",
                'range' => "global",
                'section' => "DELOREAN",
                'description' => "Should we perform a cleanup after each store action?"
            ));
        }
    }

    public function down()
    {
        Config::get()->delete("DELOREAN_CLEANUP_ALWAYS");
    }

}
