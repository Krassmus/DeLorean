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
        $deleting = Config::get()->DELOREAN_DELETE_MEMORY;
        if ($deleting) {
            SormVersion::deleteBySQL("mkdate < UNIX_TIMESTAMP() - ?", array($deleting * 86400));
        }
        $deleting = Config::get()->DELOREAN_MAKE_USERIDS_ANONYMOUS;
        if ($deleting) {
            $statement = DBManager::get()->prepare("
                UPDATE sorm_versions
                SET user_id = null
                WHERE user_id IS NOT NULL
                    AND mkdate < UNIX_TIMESTAMP() - ?
            ");
            $statement->execute(array($deleting));
        }
    }
}
