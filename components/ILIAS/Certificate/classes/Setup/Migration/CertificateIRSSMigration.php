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
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\Certificate\File\ilCertificateTemplateStakeholder;

class CertificateIRSSMigration implements Migration
{
    public const NUMBER_OF_STEPS = 1;
    private ilDBInterface $db;
    private IRSS $irss;
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
        $this->irss = $DIC->resourceStorage();
        $this->filesystem = $DIC->filesystem();
        $this->stakeholder = new ilCertificateTemplateStakeholder();
    }

    /**
     * @throws ilDatabaseException
     */
    public function step(Environment $environment): void
    {
        $query = '
            SELECT path, GROUP_CONCAT(id ORDER BY id) AS ids
            FROM (
                SELECT id, background_image_identification AS path FROM il_cert_user_cert
                UNION ALL
                SELECT id, thumbnail_image_identification AS path FROM il_cert_user_cert
            ) AS t
            WHERE path LIKE "%/%"
            GROUP BY path;
        ';
        $result = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($result)) {
            $this->updateCertificateTemplate($row['path'] ?? '', 'il_cert_user_cert');
        }

        $query = '
            SELECT path, GROUP_CONCAT(id ORDER BY id) AS ids
            FROM (
                SELECT id, background_image_identification AS path FROM il_cert_template
                UNION ALL
                SELECT id, thumbnail_image_identification AS path FROM il_cert_template
            ) AS t
            WHERE path LIKE "%/%"
            GROUP BY path;
        ';
        $result = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($result)) {
            $this->updateCertificateTemplate($row['path'] ?? '', 'il_cert_template');
        }
    }

    public function updateCertificateTemplate(string $filepath, string $table): void
    {
        if ($this->filesystem->web()->has($filepath)) {
            $rid = $this->irss->manage()->stream($this->filesystem->web()->readStream($filepath), $this->stakeholder);

            $query = "
                UPDATE $table
                SET background_image_identification = CASE
                               WHEN background_image_identification = $filepath THEN $rid
                               ELSE background_image_identification
                            END,
                    thumbnail_image_identification = CASE
                               WHEN thumbnail_image_identification = $filepath THEN $rid
                               ELSE thumbnail_image_identification
                            END
                WHERE background_image_identification = $filepath OR thumbnail_image_identification = $filepath
                ";
            $this->db->manipulate($query);
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        $result = $this->db->query(
            '
                    SELECT COUNT(id) AS count
                    FROM (
                        SELECT id, background_image_identification AS path FROM il_cert_user_cert
                        UNION ALL
                        SELECT id, thumbnail_image_identification AS path FROM il_cert_user_cert
                    ) AS t
                    WHERE path LIKE "%/%"
                    GROUP BY path;
            '
        );
        $row = $this->db->fetchAssoc($result);

        $certs = (int) $row['count'];

        $result = $this->db->query(
            '
                    SELECT COUNT(id) AS count
                    FROM (
                        SELECT id, background_image_identification AS path FROM il_cert_template
                        UNION ALL
                        SELECT id, thumbnail_image_identification AS path FROM il_cert_template
                    ) AS t
                    WHERE path LIKE "%/%"
                    GROUP BY path;
            '
        );
        $row = $this->db->fetchAssoc($result);

        $certs += (int) $row['count'];

        return (int) ceil($certs / self::NUMBER_OF_STEPS);
    }
}
