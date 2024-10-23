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

use ilDatabaseException;
use ilDatabaseUpdatedObjective;
use ilDBConstants;
use ilDBPdoInterface;
use ilDBStatement;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use JsonException;
use ReflectionClass;
use ILIAS\Data\UUID\Factory;
use PDOException;

class CertificateIdMigration implements Migration
{
    public const NUMBER_OF_STEPS = 5;
    public const NUMBER_OF_CERTS_PER_STEP = 100000;

    private ilDBPdoInterface $db;
    private Factory $uuid_factory;
    private ilDBStatement $prepared_statement;

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
        $this->prepared_statement = $this->db->prepareManip(
            'UPDATE il_cert_user_cert SET certificate_id = ?, template_values = ? WHERE id = ?',
            [
                ilDBConstants::T_TEXT,
                ilDBConstants::T_CLOB,
                ilDBConstants::T_INTEGER
            ]
        );

        $this->uuid_factory = new Factory();
    }

    /**
     * @throws JsonException
     * @throws ilDatabaseException
     */
    public function step(Environment $environment): void
    {
        $this->db->setLimit(self::NUMBER_OF_CERTS_PER_STEP);
        $result = $this->db->query(
            'SELECT id, template_values FROM il_cert_user_cert WHERE certificate_id = ' .
                $this->db->quote('-', ilDBConstants::T_TEXT)
        );

        while ($row = $this->db->fetchAssoc($result)) {
            try {
                $template_values = json_decode(
                    $row['template_values'] ?? json_encode([], JSON_THROW_ON_ERROR),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            } catch (JsonException) {
                $template_values = [];
            }
            $certificate_id = $this->uuid_factory->uuid4AsString();

            $template_values['CERTIFICATE_ID'] = $certificate_id;

            $this->db->execute($this->prepared_statement, [
                $certificate_id,
                json_encode($template_values, JSON_THROW_ON_ERROR),
                (int) $row['id']
            ]);
        }

        $this->applyUniqueConstraint();
    }

    private function applyUniqueConstraint(): void
    {
        try {
            $this->db->addUniqueConstraint('il_cert_user_cert', ['certificate_id'], 'c1');
        } catch (ilDatabaseException|PDOException) {
            // Nothing to do
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        $result = $this->db->query(
            'SELECT COUNT(id) AS missing_cert_id FROM il_cert_user_cert WHERE certificate_id = ' .
                $this->db->quote('-', ilDBConstants::T_TEXT)
        );
        $row = $this->db->fetchAssoc($result);

        $remaining_certs = (int) $row['missing_cert_id'];
        if ($remaining_certs === 0) {
            $this->applyUniqueConstraint();
        }

        return (int) ceil($remaining_certs / self::NUMBER_OF_CERTS_PER_STEP);
    }
}
