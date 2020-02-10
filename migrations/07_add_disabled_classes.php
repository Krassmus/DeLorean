<?php

class AddDisabledClasses extends Migration {

    public function up() {
        Config::get()->create("DELOREAN_DISABLED_CLASSES", array(
            'value' => "PersonalNotifications\nPersonalNotificationsUser\nMessage\nMessageUser\nUserConfigEntry\nMailQueueEntry\nLogEvent\nStudip\\Activity\\Activity",
            'type' => "string",
            'range' => "global",
            'section' => "DELOREAN",
            'description' => "Which SORM-classes should be disabled and not be tracked by DeLorean?"
        ));
        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        Config::get()->delete("DELOREAN_DISABLED_CLASSES");
    }

}
