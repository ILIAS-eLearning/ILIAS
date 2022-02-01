<?php declare(strict_types=1);

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
 ********************************************************************
 */

use ILIAS\Setup;

/**
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilComponentFactoryExistsObjective implements Setup\Objective
{
    /**
     * @inheritdoc
     */
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    /**
     * @inheritdoc
     */
    public function getLabel() : string
    {
        return "ilComponentFactory is initialized and stored into the environment.";
    }

    /**
     * @inheritdoc
     */
    public function isNotable() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new \ilDatabaseUpdatedObjective(),
            new \ilComponentRepositoryExistsObjective()
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $component_repository = $environment->getResource(Setup\Environment::RESOURCE_COMPONENT_REPOSITORY);

        $component_factory = new ilComponentFactoryImplementation(
            $component_repository,
            $db
        );

        return $environment->withResource(
            Setup\Environment::RESOURCE_COMPONENT_FACTORY,
            $component_factory
        );
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return is_null($environment->getResource(Setup\Environment::RESOURCE_COMPONENT_FACTORY));
    }
}
