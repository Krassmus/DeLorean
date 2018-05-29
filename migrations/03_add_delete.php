<?php

class AddDelete extends Migration {
    
    public function up() {
        DBManager::get()->exec("
            ALTER TABLE `sorm_versions` 
            ADD `delete` TINYINT DEFAULT '0' AFTER `file_id`
        ");
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


        $new_job = array(
            'filename'    => 'public/plugins_packages/RasmusFuhse/DeLorean/cleanup_delorean.cronjob.php',
            'class'       => 'CleanupDelorean',
            'priority'    => 'normal',
            'minute'      => '23',
            'hour'        => '4'
        );
        $query = "INSERT IGNORE INTO `cronjobs_tasks`
                    (`task_id`, `filename`, `class`, `active`)
                  VALUES (:task_id, :filename, :class, 1)";
        $task_statement = DBManager::get()->prepare($query);

        $query = "INSERT IGNORE INTO `cronjobs_schedules`
                    (`schedule_id`, `task_id`, `parameters`, `priority`,
                     `type`, `minute`, `hour`, `mkdate`, `chdate`,
                     `last_result`)
                  VALUES (:schedule_id, :task_id, '[]', :priority, 'periodic',
                          :minute, :hour, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                          NULL)";
        $schedule_statement = DBManager::get()->prepare($query);

        $task_id = md5(uniqid('task', true));

        $task_statement->execute(array(
            ':task_id'  => $task_id,
            ':filename' => $new_job['filename'],
            ':class'    => $new_job['class'],
        ));

        $schedule_id = md5(uniqid('schedule', true));
        $schedule_statement->execute(array(
            ':schedule_id' => $schedule_id,
            ':task_id'     => $task_id,
            ':priority'    => $new_job['priority'],
            ':minute'      => $new_job['minute'],
            ':hour'        => $new_job['hour']
        ));

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        Config::get()->delete("DELOREAN_DELETE_MEMORY");
    }

}