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
        require_once __DIR__."/lib/SormVersion.php";
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
        $filespace = SormVersion::getAllocatedFileSpace();
        $dbspace = SormVersion::getAllocatedDBSpace();
        SormVersion::cleanDBUp();
        echo "Vorher: ".$this->getSizeName($dbspace) . " Datenbank und " . $this->getSizeName($filespace). " Dateien.\n";
        echo "Nachher: ".$this->getSizeName(SormVersion::getAllocatedFileSpace()) . " Datenbank und " . $this->getSizeName(SormVersion::getAllocatedFileSpace()). " Dateien.\n";

        //verwaiste Dateien löschen:
        $folder = SormVersion::getFileDataPath();
        $filesize = 0;
        if ($folder) {
            $files = array_diff(scandir($folder), array('.', '..'));
            foreach ($files as $file) {
                if (file_exists($folder . "/" . $file)) {
                    $version = SormVersion::findOneBySQL("file_id = ? LIMIT 1", array($file));
                    if (!$version) {
                        $size = filesize($folder . "/" . $file);
                        $success = @unlink($folder . "/" . $file);
                        if (!$success) {
                            echo sprintf("Konnte Datei %s nicht löschen", $folder . "/" . $file) . "\n";
                        } else {
                            $filesize += $size;
                        }
                    }
                }
            }
        }
        echo "Verwaiste Dateien: ".$this->getSizeName($filesize). " an Dateien konnten gelöscht werden.";
    }

    protected function getSizeName($bytes)
    {
        if ($bytes < 1024) {
            return $bytes . " Bytes";
        } elseif ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 2) . " KB";
        } elseif ($bytes < 1024 * 1024 * 1024) {
            return round($bytes / 1024 / 1024, 2) . " MB";
        } elseif ($bytes < 1024 * 1024 * 1024 * 1024) {
            return round($bytes / 1024 / 1024 / 1024, 2) . " GB";
        } else {
            return round($bytes / 1024 / 1024 / 1024 / 1024, 2) . " TB";
        }
    }
}
