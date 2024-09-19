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
use ILIAS\Refinery;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Certificate\Setup\Migration\CertificateIdMigration;
use ILIAS\Certificate\Setup\Migration\CertificateIRSSMigration;

class ilCertificatSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): Refinery\Transformation
    {
        throw new LogicException('Agent has no config.');
    }

    public function getInstallObjective(Setup\Config $config = null): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        return new ObjectiveCollection(
            'Database is updated for component/ILIAS/Certificate',
            true,
            new ilDatabaseUpdateStepsExecutedObjective(new ilCertificateDatabaseUpdateSteps()),
            new ilDatabaseUpdateStepsExecutedObjective(new MigrateCourseCertificateProviderDBUpdateSteps()),
            new ilDatabaseUpdateStepsExecutedObjective(new MigrateExerciseCertificateProviderDBUpdateSteps()),
        );
    }

    public function getBuildObjective(): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new ObjectiveCollection(
            'Database is updated for component/ILIAS/Certificate',
            true,
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilCertificateDatabaseUpdateSteps()),
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new MigrateCourseCertificateProviderDBUpdateSteps()),
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new MigrateExerciseCertificateProviderDBUpdateSteps()),
        );
    }

    public function getMigrations(): array
    {
        return [
            new CertificateIdMigration(),
            new CertificateIRSSMigration()
        ];
    }
}
