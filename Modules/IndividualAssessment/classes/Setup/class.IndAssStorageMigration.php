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

use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\Setup\CLI\IOWrapper;

class IndAssStorageMigration implements Setup\Migration
{
    private const DEFAULT_AMOUNT_OF_STEPS = 200;
    private ilDBInterface $db;
    private ILIAS\DI\Container $dic;

    /**
     * @var IOWrapper
     */
    private mixed $io;

    public function getLabel(): string
    {
        return "Migrate FSStorage to IRSS";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return self::DEFAULT_AMOUNT_OF_STEPS;
    }

    public function getPreconditions(Environment $environment): array
    {
        return array_merge(
            \ilResourceStorageMigrationHelper::getPreconditions(),
            [
                new \ilSettingsFactoryExistsObjective()
            ]
        );
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $settings_factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        $DIC = $GLOBALS["DIC"];
        $GLOBALS["DIC"] = new ILIAS\DI\Container();
        $GLOBALS["DIC"]["ilDB"] = $this->db;
        $GLOBALS["DIC"]["ilSetting"] = $settings_factory->settingsFor();
        $GLOBALS["DIC"]["ilClientIniFile"] = $client_ini;

        ILIAS\FileDelivery\Init::init($GLOBALS["DIC"]);
        ilInitialisation::bootstrapFilesystems();

        $this->dic = $GLOBALS["DIC"];
        $GLOBALS["DIC"] = $DIC;

        $stakeholder = new ilIndividualAssessmentGradingStakeholder();
        $this->helper = new \ilResourceStorageMigrationHelper(
            $stakeholder,
            $environment
        );
    }

    /**
     * @throws Exception
     */
    public function step(Environment $environment): void
    {
        $GLOBALS["DIC"] = $this->dic;

        $query = "SELECT obj_id, usr_id, file_name FROM iass_members WHERE file_name LIKE '%.%' LIMIT 1;";
        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);

        $obj_id = (int)$row['obj_id'];
        $usr_id = (int)$row['usr_id'];
        $fs_storage = ilIndividualAssessmentFileStorage::getInstance($obj_id);
        $fs_storage->setUserId($usr_id);
        $filepath = $fs_storage->getAbsolutePath() . '/' . $row['file_name'];

        $resource_id = $this->helper->movePathToStorage($filepath, 6);
        if(! $resource_id) {
            throw new \Exception('not stored:' . $filepath);
        }

        $identifier = $resource_id->serialize();
        $query = "UPDATE iass_members SET file_name = '$identifier' WHERE obj_id = $obj_id AND usr_id = $usr_id";
        $this->db->manipulate($query);
    }

    public function getRemainingAmountOfSteps(): int
    {
        $query = "SELECT COUNT(*) AS amount FROM iass_members WHERE file_name LIKE '%.%';";
        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);
        return (int) $row['amount'];
    }
}
