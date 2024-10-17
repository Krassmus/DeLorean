<?php

class OptionallyChangeDataPath extends Migration {

    public function up() {
        if (!Config::get()->offsetExists("DELOREAN_DATA_PATH")) {
            Config::get()->create("DELOREAN_DATA_PATH", array(
                'value' => "",
                'type' => "string",
                'range' => "global",
                'section' => "DELOREAN",
                'description' => "Usually (when this value is empty) the files will be saved by DeLorean to the \$GLOBALS['STUDIP_BASE_PATH'] . '/data/delorean_files', but you can set this option to any other absolute path."
            ));
        }
        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        //Will be deleted in migration 01 down-part.
    }

}
