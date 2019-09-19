<?php

class CleanupDelorean extends CronJob
{
    /**
     * Returns the name of the cronjob.
     */
    public static function getName()
    {
        return _('DeLorean Aufräumer');
    }

    /**
     * Returns the description of the cronjob.
     */
    public static function getDescription()
    {
        return _('Räumt die Tabelle des DeLorean Plugins auf, wenn dies gewünscht ist und anonymisiert die Datensätze gegebenenfalls.');
    }

    public function setUp() {
        require_once __DIR__."/classes/SormVersion.class.php";
    }

    /**
     * Executes the cronjob.
     *
     * @param mixed $last_result What the last execution of this cronjob
     *                           returned.
     * @param Array $parameters Parameters for this cronjob instance which
     *                          were defined during scheduling.
     *                          Only valid parameter at the moment is
     *                          "verbose" which toggles verbose output while
     *                          purging the cache.
     */
    public function execute($last_result, $parameters = array())
    {
        SormVersion::cleanDBUp();
        //verwaiste Dateien löschen:
        $folder = self::getFileDataPath();
        if ($folder) {
            $files = array_diff(scandir($folder), array('.', '..'));
            foreach ($files as $file) {
                if (file_exists($folder . "/" . $file)) {
                    $version = self::findOneBySQL("file_id = ? LIMIT 1", array($file));
                    if (!$version) {
                        @unlink($folder . "/" . $file);
                    }
                }
            }
        }
    }
}
