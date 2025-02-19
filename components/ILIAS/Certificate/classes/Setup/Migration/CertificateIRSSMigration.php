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

namespace ILIAS\Certificate\Setup\Migration;

use ilDBConstants;
use ilDBInterface;
use ReflectionClass;
use ilDatabaseException;
use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;
use ILIAS\Setup\CLI\IOWrapper;
use ilDatabaseUpdatedObjective;
use ilResourceStorageMigrationHelper;
use ILIAS\Certificate\File\ilCertificateTemplateStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class CertificateIRSSMigration implements Migration
{
    public const NUMBER_OF_STEPS = 10;
    public const NUMBER_OF_PATHS_PER_STEP = 10;
    public const TABLE_TEMPLATE_CERTIFICATES = 'il_cert_template';
    public const TABLE_USER_CERTIFICATES = 'il_cert_user_cert';
    private ilResourceStorageMigrationHelper $helper;
    private ilDBInterface $db;
    private ilCertificateTemplateStakeholder $stakeholder;
    private ?IOWrapper $io = null;

    public function getLabel(): string
    {
        return (new ReflectionClass($this))->getShortName();
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return self::NUMBER_OF_STEPS;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseUpdatedObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $this->helper = new ilResourceStorageMigrationHelper(new ilCertificateTemplateStakeholder(), $environment);
        $this->stakeholder = new ilCertificateTemplateStakeholder();
        $io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);
        if ($io instanceof IOWrapper) {
            $this->io = $io;
        }
    }

    /**
     * @throws ilDatabaseException
     */
    public function step(Environment $environment): void
    {
        $this->migrateGlobalCertificateBackgroundImage(true);
        $remaining_paths = $this->stepCertificates(self::NUMBER_OF_PATHS_PER_STEP, self::TABLE_TEMPLATE_CERTIFICATES);
        if ($remaining_paths > 0) {
            $this->stepCertificates($remaining_paths, self::TABLE_USER_CERTIFICATES);
        }
    }

    public function stepCertificates(int $remaining_paths, string $table): int
    {
        $this->db->setLimit($remaining_paths);
        $query = '
            SELECT path
            FROM (
                     SELECT id, background_image_path AS path FROM ' . $this->db->quoteIdentifier($table) . '
                            WHERE background_image_ident IS NULL OR background_image_ident = \'\'
                     UNION ALL
                     SELECT id, thumbnail_image_path AS path FROM ' . $this->db->quoteIdentifier($table) . '
                            WHERE thumbnail_image_ident IS NULL OR thumbnail_image_ident = \'\'
                 ) AS t
            GROUP BY path
            HAVING path IS NOT NULL AND path != \'\'
        ';
        $result = $this->db->query($query);
        $paths = $this->db->numRows($result);
        if ($paths > 0) {
            while ($row = $this->db->fetchAssoc($result)) {
                $this->updateCertificatePathFromTable($row['path'] ?? '', $table);
            }
            $remaining_paths -= self::NUMBER_OF_PATHS_PER_STEP - $paths;
        }

        return $remaining_paths;
    }

    public function migrateGlobalCertificateBackgroundImage(bool $hotrun = false): int
    {
        $result = $this->db->queryF(
            'SELECT value FROM settings WHERE module = %s AND keyword = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['certificate', 'cert_bg_image']
        );
        $row = $this->db->fetchAssoc($result);

        if (!isset($row['value']) || $row['value'] === '') {
            return 0;
        }

        $path_to_file = ILIAS_ABSOLUTE_PATH . '/' . ILIAS_WEB_DIR . '/' . CLIENT_ID . '/certificates/default/' . $row['value'];
        if (!is_file($path_to_file)) {
            return 0;
        }

        if (!$hotrun) {
            return 1;
        }

        $this->inform("Migrating global default certificate background image: $path_to_file");
        $resource_id = $this->helper->movePathToStorage(
            $path_to_file,
            $this->stakeholder->getOwnerOfNewResources(),
            null,
            null,
            true
        );

        $image_ident = '-';
        if ($resource_id instanceof ResourceIdentification) {
            $image_ident = $resource_id->serialize();
            $this->inform(
                "IRSS identification for global default certificate background image: $image_ident",
                true
            );
        } else {
            $this->error(
                'IRSS returned NULL as identification when trying to move global default background image ' .
                "file $path_to_file to the storage service."
            );
        }

        $this->updateDefaultBackgroundImagePaths(
            '/certificates/default/' . $row['value'],
            $image_ident
        );

        $query = '
                        UPDATE settings
                        SET value = %s
                        WHERE module = %s AND keyword = %s';
        $this->db->manipulateF(
            $query,
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$image_ident, 'certificate', 'cert_bg_image']
        );

        return 0;
    }

    public function updateCertificatePathFromTable(string $filepath, string $table): void
    {
        if (!$filepath) {
            return;
        }

        $sanitized_filepath = ltrim($filepath);
        if (str_starts_with($filepath, '/')) {
            $sanitized_filepath = substr($sanitized_filepath, 1);
        }
        $full_path = ILIAS_ABSOLUTE_PATH . '/' . ILIAS_WEB_DIR . '/' . CLIENT_ID . '/' . $sanitized_filepath;

        $resource_id = $this->helper->movePathToStorage(
            $full_path,
            $this->stakeholder->getOwnerOfNewResources(),
            null,
            null,
            true
        );

        $image_ident = '-';
        if ($resource_id instanceof ResourceIdentification) {
            $image_ident = $resource_id->serialize();
            $this->inform(
                "IRSS identification for image path $full_path when migrating table $table: $image_ident" .
                ($table === self::TABLE_TEMPLATE_CERTIFICATES ? "\n" . self::TABLE_USER_CERTIFICATES . ": $image_ident" : ''),
                true
            );
        } else {
            $this->error(
                'IRSS returned NULL as identification when trying to move ' .
                "file $full_path to the storage service for table $table." .
                ($table === self::TABLE_TEMPLATE_CERTIFICATES ? "\n" . self::TABLE_USER_CERTIFICATES . ": $image_ident" : '')
            );
        }

        $query = "
                UPDATE {$this->db->quoteIdentifier($table)}
                SET background_image_ident = %s WHERE background_image_path = %s;";
        $this->db->manipulateF(
            $query,
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$image_ident, $filepath]
        );
        $query = "
                UPDATE {$this->db->quoteIdentifier($table)}
                SET thumbnail_image_ident = %s WHERE thumbnail_image_path = %s;";
        $this->db->manipulateF(
            $query,
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$image_ident, $filepath]
        );
        if ($table === self::TABLE_TEMPLATE_CERTIFICATES) {
            $query = "
                UPDATE {$this->db->quoteIdentifier(self::TABLE_USER_CERTIFICATES)}
                SET background_image_ident = %s WHERE background_image_path = %s;";
            $this->db->manipulateF(
                $query,
                [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
                [$image_ident, $filepath]
            );
            $query = "
                UPDATE {$this->db->quoteIdentifier(self::TABLE_USER_CERTIFICATES)}
                SET thumbnail_image_ident = %s WHERE thumbnail_image_path = %s;";
            $this->db->manipulateF(
                $query,
                [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
                [$image_ident, $filepath]
            );
        }

        if ($image_ident !== '-') {
            $query = "
                    UPDATE {$this->db->quoteIdentifier($table)}
                    SET background_image_path = NULL WHERE background_image_path = %s;";
            $this->db->manipulateF(
                $query,
                [ilDBConstants::T_TEXT],
                [$filepath]
            );
            $query = "
                    UPDATE {$this->db->quoteIdentifier($table)}
                    SET thumbnail_image_path = NULL WHERE thumbnail_image_path = %s;";
            $this->db->manipulateF(
                $query,
                [ilDBConstants::T_TEXT],
                [$filepath]
            );
            if ($table === self::TABLE_TEMPLATE_CERTIFICATES) {
                $query = "
                    UPDATE {$this->db->quoteIdentifier(self::TABLE_USER_CERTIFICATES)}
                    SET background_image_path = NULL WHERE background_image_path = %s;";
                $this->db->manipulateF(
                    $query,
                    [ilDBConstants::T_TEXT],
                    [$filepath]
                );
                $query = "
                    UPDATE {$this->db->quoteIdentifier(self::TABLE_USER_CERTIFICATES)}
                    SET thumbnail_image_path = NULL WHERE thumbnail_image_path = %s;";
                $this->db->manipulateF(
                    $query,
                    [ilDBConstants::T_TEXT],
                    [$filepath]
                );
            }
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        $paths = $this->migrateGlobalCertificateBackgroundImage(false);

        $result = $this->db->query(
            '
                    SELECT COUNT(*) AS count
                    FROM (
                        SELECT path
                        FROM (
                            SELECT background_image_path AS path FROM il_cert_user_cert
                            WHERE background_image_ident IS NULL OR background_image_ident = \'\'
                            UNION ALL
                            SELECT thumbnail_image_path AS path FROM il_cert_user_cert
                            WHERE thumbnail_image_ident IS NULL OR thumbnail_image_ident = \'\'
                            UNION ALL
                            SELECT background_image_path AS path FROM il_cert_template
                            WHERE background_image_ident IS NULL OR background_image_ident = \'\'
                            UNION ALL
                            SELECT thumbnail_image_path AS path FROM il_cert_template
                            WHERE thumbnail_image_ident IS NULL OR thumbnail_image_ident = \'\'
                        ) AS t
                        GROUP BY path
                        HAVING path IS NOT NULL AND path != \'\'
                    ) AS t;
            '
        );
        $row = $this->db->fetchAssoc($result);

        $paths += (int) ($row['count'] ?? 0);
        $num_steps = (int) ceil($paths / self::NUMBER_OF_STEPS);

        $this->inform(
            "Remaining certificate background image/tile image paths: $paths / Number of steps: $num_steps",
            true
        );

        return $num_steps;
    }

    public function updateDefaultBackgroundImagePaths(string $old_relative_path, string $new_rid): void
    {
        $this->db->manipulateF(
            '
                    UPDATE il_cert_template SET background_image_ident = %s 
                        WHERE currently_active = 1 AND (background_image_path = %s OR background_image_path = %s )
                        AND background_image_ident IS NULL OR background_image_ident = \'\'',
            [
                ilDBConstants::T_TEXT,
                ilDBConstants::T_TEXT,
                ilDBConstants::T_TEXT
            ],
            [
                $new_rid,
                $old_relative_path,
                '/certificates/default/background.jpg'
            ]
        );

        $this->db->manipulateF(
            '
                    UPDATE il_cert_user_cert SET background_image_ident = %s 
                         WHERE currently_active = 1 AND (background_image_path = %s OR background_image_path = %s )
                         AND background_image_ident IS NULL OR background_image_ident = \'\'',
            [
                ilDBConstants::T_TEXT,
                ilDBConstants::T_TEXT,
                ilDBConstants::T_TEXT
            ],
            [
                $new_rid,
                $old_relative_path,
                '/certificates/default/background.jpg'
            ]
        );
    }

    private function inform(string $text, bool $force = false): void
    {
        if ($this->io === null || (!$force && !$this->io->isVerbose())) {
            return;
        }

        $this->io->inform($text);
    }

    private function error(string $text): void
    {
        if ($this->io === null) {
            return;
        }

        $this->io->error($text);
    }
}
