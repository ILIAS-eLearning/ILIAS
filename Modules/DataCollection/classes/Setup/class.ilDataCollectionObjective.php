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

use ILIAS\DI\Container;
use ILIAS\Setup\Environment;

class ilDataCollectionObjective extends ilDatabaseUpdateStepsExecutedObjective
{
    /** @var ilDataCollectionDBUpdateSteps9 */
    protected ilDatabaseUpdateSteps $steps;

    public function __construct(ilDatabaseUpdateSteps $steps)
    {
        if ($steps instanceof ilDataCollectionDBUpdateSteps9) {
            parent::__construct($steps);
        } else {
            throw new InvalidArgumentException('$steps must be instance of ilDataCollectionDBUpdateSteps9');
        }
    }

    public function getPreconditions(Environment $environment): array
    {
        return array_merge(
            parent::getPreconditions($environment),
            [
                new ilComponentFactoryExistsObjective(),
                new ilComponentRepositoryExistsObjective()
            ]
        );
    }

    public function achieve(Environment $environment): Environment
    {
        global $DIC;
        $DIC = new Container();
        $DIC['lng'] = new ilSetupLanguage('en');
        $DIC['ilDB'] = $environment->getResource(Environment::RESOURCE_DATABASE);
        $DIC['component.factory'] = $environment->getResource(Environment::RESOURCE_COMPONENT_FACTORY);
        $DIC['component.repository'] = $environment->getResource(Environment::RESOURCE_COMPONENT_REPOSITORY);
        $DIC['ilLoggerFactory'] = ilLoggerFactory::getInstance();
        return parent::achieve($environment);
    }
}
