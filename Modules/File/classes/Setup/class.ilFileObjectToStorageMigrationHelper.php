<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
class ilFileObjectToStorageMigrationHelper
{
    protected string $base_path = '/var/iliasdata/ilias/default/ilFile';
    public const MIGRATED = ".migrated";
    protected ilDBInterface $database;

    /**
     * @param string        $base_path
     * @param ilDBInterface $database
     */
    public function __construct(string $base_path, ilDBInterface $database)
    {
        $this->base_path = $base_path;
        $this->database = $database;
    }

    public function getNext() : ilFileObjectToStorageDirectory
    {
        do {
            $next_id = $this->getNextFileId();
            $path = $this->createPathFromId($next_id);
            $path_found = file_exists($path);
            if (!$path_found) {
                $this->database->update(
                    'file_data',
                    ['rid' => ['text', 'unknown']],
                    ['file_id' => ['integer', $next_id]]
                );
            } else {
                return new ilFileObjectToStorageDirectory($next_id, $path);
            }
        } while (!$path_found);
    }

    private function getNextFileId() : int
    {
        $query = "SELECT file_id 
                    FROM file_data 
                    WHERE 
                        (rid IS NULL OR rid = '')
                        AND (file_id != ''  AND file_id IS NOT NULL) 
                    LIMIT 1;";
        $r = $this->database->query($query);
        $d = $this->database->fetchObject($r);
        if (!isset($d->file_id) || null === $d->file_id || '' === $d->file_id) {
            throw new LogicException("error fetching file_id");
        }

        return (int) $d->file_id;
    }


    private function createPathFromId(int $file_id) : string
    {
        $path = [];
        $found = false;
        $num = $file_id;
        $path_string = '';
        for ($i = 3; $i > 0; $i--) {
            $factor = pow(100, $i);
            if (($tmp = (int) ($num / $factor)) or $found) {
                $path[] = $tmp;
                $num = $num % $factor;
                $found = true;
            }
        }

        if (count($path)) {
            $path_string = (implode('/', $path) . '/');
        }

        return $this->base_path . '/' . $path_string . 'file_' . $file_id;
    }
}
