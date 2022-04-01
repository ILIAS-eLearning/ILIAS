<?php
declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilArrayTableDataParser
{
    protected string $dir;

    protected string $value = '';

    public function __construct(string $data_dir)
    {
        $this->dir = $data_dir;
    }

    /**
     * @return bool|void
     */
    public function startParsing()
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilLogger = $DIC->logger()->root();

        if (!$dp = opendir($this->dir)) {
            $ilLogger->error(__METHOD__ . ': Cannot open data directory: ' . $this->dir);
            return false;
        }

        $ilLogger->log(__METHOD__ . ': Reading table data from: ' . $this->dir);
        while (false !== ($file = readdir($dp))) {
            $ilLogger->log(__METHOD__ . ': Handling file: ' . $file);
            if (substr($file, -5) !== '.data') {
                $ilLogger->log(__METHOD__ . ': Ignoring file: ' . $file);
                continue;
            }

            $content = file_get_contents($this->dir . DIRECTORY_SEPARATOR . $file);

            $ilLogger->log(__METHOD__ . ': Reading inserts of ' . $this->dir . '/' . $file);
            $content = unserialize($content);//PHP8Review: Specifiy the unserialized content/classes

            if (!is_array($content)) {
                $ilLogger->log(__METHOD__ . ': No entries found in ' . $this->dir . '/' . $file);
                continue;
            }

            foreach ($content as $table => $rows) {
                foreach ($rows as $row) {
                    $ilDB->insert($table, $row);
                }
            }
            if (function_exists('memory_get_usage')) {
                $ilLogger->log(__METHOD__ . ': Memory usage ' . memory_get_usage(true));
            }
        }
        fclose($dp);
    }
}
