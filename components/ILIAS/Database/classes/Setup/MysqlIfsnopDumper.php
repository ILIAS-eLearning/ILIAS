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

declare(strict_types=1);

class MysqlIfsnopDumper implements MysqlDumper
{
    public const FILE_NAME = "dump.sql";

    protected ?string $export_hooks_path;

    public function __construct(?string $export_hooks_path)
    {
        $this->export_hooks_path = $export_hooks_path;
    }

    public function createDump(
        string $host,
        string $user,
        string $password,
        string $name,
        string $port,
        string $target
    ): void {
        if (!is_null($this->export_hooks_path) && !is_readable($this->export_hooks_path)) {
            throw new Exception("Export hooks file '$this->export_hooks_path' is not readable.");
        }

        try {
            $dumper = new Ifsnop\Mysqldump\Mysqldump(
                "mysql:host=$host;port=$port;dbname=$name",
                $user,
                $password,
                ['add-drop-table' => true]
            );
            if (!is_null($this->export_hooks_path)) {
                include $this->export_hooks_path;
            }
            $dumper->start($target . "/" . self::FILE_NAME);
        } catch (\Exception $e) {
            throw new Exception("Error during sql dump: " . $e->getMessage());
        }
    }
}
