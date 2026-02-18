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

use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\UnachievableException;

class ilDatabaseEnvironmentValidObjective implements Objective
{
    /**
     * @var string
     */
    private const ROW_FORMAT_DYNAMIC = "DYNAMIC";
    /**
     * @var string
     */
    private const INNO_DB = "InnoDB";

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "The database server has valid settings.";
    }

    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @return array<\ilDatabaseServerIsConnectableObjective|\ilDatabaseCreatedObjective>
     */
    public function getPreconditions(Environment $environment): array
    {
        return [ ];
    }

    public function achieve(Environment $environment): Environment
    {
        /**
         * @var $db ilDBInterface
         * @var $io Setup\CLI\IOWrapper
         */
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);
        $this->checkDBAvailable($db);
        $this->checkRowFormat($db);
        $io->inform("Default Row Format is " . self::ROW_FORMAT_DYNAMIC . ".");
        $this->checkDefaultEngine($db);
        $io->inform("Default Engine is InnoDB.");

        return $environment;
    }

    protected function checkDefaultEngine(ilDBInterface $db)
    {
        $default_engine = 'unknown';
        try {
            $r = $db->query('SHOW ENGINES ');
            while ($d = $db->fetchObject($r)) {
                if (strtoupper((string) $d->Support) === 'DEFAULT') {
                    $default_engine = strtolower((string) $d->Engine);
                    break;
                }
            }
        } catch (Throwable) {
        }
        $default_engine = strtolower($default_engine);

        if ($default_engine !== strtolower(self::INNO_DB)) {
            throw new UnachievableException(
                "The default database engine is not set to '" . self::INNO_DB
                . ", `$default_engine` given'. Please set the default database engine to '"
                . self::INNO_DB . " to proceed'."
            );
        }
    }

    protected function checkRowFormat(ilDBInterface $db): void
    {
        $setting = $db->fetchObject($db->query('SELECT @@GLOBAL.innodb_default_row_format AS row_format;'));
        $row_format = $setting->row_format ?? null;
        if ($row_format === null || strtoupper((string) $row_format) !== self::ROW_FORMAT_DYNAMIC) {
            throw new UnachievableException(
                "The default row format of the database is not set to '" . self::ROW_FORMAT_DYNAMIC . "'. Please set the default row format to " . self::ROW_FORMAT_DYNAMIC . " and run an 'OPTIMIZE TABLE' for each of your database tables before you continue."
            );
        }
    }

    /**
     * @param ilDBInterface $db
     * @return void
     */
    protected function checkDBAvailable(?ilDBInterface $db): void
    {
        if ($db === null) {
            throw new UnachievableException(
                "Database cannot be connected. Please check the credentials."
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Environment $environment): bool
    {
        return true;
    }
}
