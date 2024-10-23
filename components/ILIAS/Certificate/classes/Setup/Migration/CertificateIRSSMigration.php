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

use ilDBInterface;
use ReflectionClass;
use ilDatabaseException;
use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;
use ilDatabaseUpdatedObjective;
use ILIAS\Filesystem\Filesystems;
use ilResourceStorageMigrationHelper;
use ILIAS\Certificate\File\ilCertificateTemplateStakeholder;

class CertificateIRSSMigration implements Migration
{
    public const NUMBER_OF_STEPS = 10;
    public const NUMBER_OF_PATHS_PER_STEP = 10;
    private ilResourceStorageMigrationHelper $helper;
    private ilDBInterface $db;
    private Filesystems $filesystem;
    private ilCertificateTemplateStakeholder $stakeholder;

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
        global $DIC;
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $this->helper = new ilResourceStorageMigrationHelper(new ilCertificateTemplateStakeholder(), $environment);
        $this->stakeholder = new ilCertificateTemplateStakeholder();
    }

    /**
     * @throws ilDatabaseException
     */
    public function step(Environment $environment): void
    {
        $remaining_paths = $this->stepUserCertificates(self::NUMBER_OF_PATHS_PER_STEP);
        if ($remaining_paths > 0) {
            $remaining_paths = $this->stepTemplateCertificates($remaining_paths);
        }
        if ($remaining_paths === 0) {
            $this->lastStep();
        }
    }

    public function stepUserCertificates(int $remaining_paths): int
    {
        $this->db->setLimit($remaining_paths);
        $query = '
            SELECT path
            FROM (
                     SELECT id, background_image_path AS path FROM il_cert_user_cert
                     UNION ALL
                     SELECT id, thumbnail_image_path AS path FROM il_cert_user_cert
                 ) AS t
            GROUP BY path
            HAVING path IS NOT NULL AND path != \'\'
        ';
        $result = $this->db->query($query);
        $paths = $this->db->numRows($result);
        if ($paths > 0) {
            while ($row = $this->db->fetchAssoc($result)) {
                $this->updateCertificatePathFromTable($row['path'] ?? '', 'il_cert_user_cert');
            }
            $remaining_paths -= self::NUMBER_OF_PATHS_PER_STEP - $paths;
        }
        return $remaining_paths;
    }

    public function stepTemplateCertificates(int $remaining_paths): int
    {
        $this->db->setLimit($remaining_paths);
        $query = '
            SELECT path
            FROM (
                     SELECT id, background_image_path AS path FROM il_cert_template
                     UNION ALL
                     SELECT id, thumbnail_image_path AS path FROM il_cert_template
                 ) AS t
            GROUP BY path
            HAVING path IS NOT NULL AND path != \'\'
        ';
        $result = $this->db->query($query);
        $paths = $this->db->numRows($result);
        if ($paths > 0) {
            while ($row = $this->db->fetchAssoc($result)) {
                $this->updateCertificatePathFromTable($row['path'] ?? '', 'il_cert_template');
            }
            $remaining_paths -= self::NUMBER_OF_PATHS_PER_STEP - $paths;
        }
        return $remaining_paths;
    }

    public function updateCertificatePathFromTable(string $filepath, string $table): void
    {
        if (!$filepath) {
            return;
        }

        $rid = $this->helper->movePathToStorage(
            ILIAS_ABSOLUTE_PATH . '/' . ILIAS_WEB_DIR . '/' . CLIENT_ID . $filepath,
            $this->stakeholder->getOwnerOfNewResources(),
            null,
            null,
            true
        );

        if (!isset($rid) || $rid === null) {
            $rid = '-';
        }

        $query = "
                UPDATE {$this->db->quoteIdentifier($table)}
                SET background_image_ident = CASE
                               WHEN background_image_path = %s THEN %s
                               ELSE background_image_path
                            END,
                    thumbnail_image_ident = CASE
                               WHEN thumbnail_image_path = %s THEN %s
                               ELSE thumbnail_image_path
                            END,
                    background_image_path = CASE
                               WHEN background_image_path = %s THEN NULL
                               ELSE background_image_path
                            END,
                    thumbnail_image_path = CASE
                               WHEN thumbnail_image_path = %s THEN NULL
                               ELSE thumbnail_image_path
                            END
                WHERE background_image_path = %s OR thumbnail_image_path = %s
                ";
        $this->db->manipulateF(
            $query,
            ['text', 'text', 'text', 'text', 'text', 'text', 'text', 'text'],
            [$filepath, $rid, $filepath, $rid, $filepath, $filepath, $filepath, $filepath]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $last_step = 1;
        if (
            !$this->db->tableColumnExists('il_cert_user_cert', 'background_image_path') ||
            !$this->db->tableColumnExists('il_cert_user_cert', 'thumbnail_image_path') ||
            !$this->db->tableColumnExists('il_cert_template', 'background_image_path') ||
            !$this->db->tableColumnExists('il_cert_template', 'thumbnail_image_path')
        ) {
            $last_step = 0;
            return 0;
        }

        $result = $this->db->query(
            '
                    SELECT COUNT(*) AS count FROM (
                    SELECT path
                    FROM (
                        SELECT id, background_image_path AS path FROM il_cert_user_cert
                        UNION ALL
                        SELECT id, thumbnail_image_path AS path FROM il_cert_user_cert
                    ) AS t
                    GROUP BY path
                    HAVING path IS NOT NULL AND path != \'\') AS t;
            '
        );
        $row = $this->db->fetchAssoc($result);

        $paths = (int) $row['count'];

        $result = $this->db->query(
            '
                    SELECT COUNT(*) AS count FROM (
                    SELECT path
                    FROM (
                        SELECT id, background_image_path AS path FROM il_cert_template
                        UNION ALL
                        SELECT id, thumbnail_image_path AS path FROM il_cert_template
                    ) AS t
                    GROUP BY path
                    HAVING path IS NOT NULL AND path != \'\') AS t;
            '
        );
        $row = $this->db->fetchAssoc($result);

        $paths += (int) $row['count'];

        $paths += $last_step;

        return (int) ceil($paths / self::NUMBER_OF_STEPS);
    }

    public function lastStep(): void
    {
        if ($this->db->tableColumnExists('il_cert_user_cert', 'background_image_path')) {
            $this->db->dropTableColumn('il_cert_user_cert', 'background_image_path');
        }
        if ($this->db->tableColumnExists('il_cert_user_cert', 'thumbnail_image_path')) {
            $this->db->dropTableColumn('il_cert_user_cert', 'thumbnail_image_path');
        }
        if ($this->db->tableColumnExists('il_cert_template', 'background_image_path')) {
            $this->db->dropTableColumn('il_cert_template', 'background_image_path');
        }
        if ($this->db->tableColumnExists('il_cert_template', 'thumbnail_image_path')) {
            $this->db->dropTableColumn('il_cert_template', 'thumbnail_image_path');
        }
    }
}
