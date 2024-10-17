<?php

class AddMaximumSize extends Migration {

    public function up() {

        if (!Config::get()->offsetExists("DELOREAN_MAX_SIZE")) {
            Config::get()->create("DELOREAN_MAX_SIZE", array(
                'value' => 1024 * 1024 * 1024 * 20,
                'type' => "integer",
                'range' => "global",
                'section' => "DELOREAN",
                'description' => "How many bytes disc space should the delorean take in a maximum? Default is 20 GB."
            ));
        }


        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        Config::get()->delete("DELOREAN_MAX_SIZE");
    }

}
