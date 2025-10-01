<?php

/**
 * This file is part of cate, a powerful training management system
 * published by CaT Concepts and Training GmbH. cate is based upon ILIAS open source e-Learning.
 *
 * cate is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case, or you just want to try cate, you'll find
 * us at:
 * https://www.cate-tms.de
 *
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Setup\Environment;

class LSODropActivationAfterMigrationObjective extends \ilDatabaseUpdateStepsExecutedObjective
{
    public function __construct(
        ilDatabaseUpdateSteps $steps,
        protected LSOMigrateActivation $migration
    ) {
        parent::__construct($steps);
    }

    /**
     * @inheritdocs
     */
    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDBStepExecutionDBExistsObjective(),
            new ilDatabaseUpdatedObjective(),
            new ilDBStepReaderExistsObjective()
        ];
    }

    public function isApplicable(Environment $environment): bool
    {
        $this->migration->prepare($environment);
        return $this->migration->getRemainingAmountOfSteps() === 0
            && parent::isApplicable($environment);
    }
}
