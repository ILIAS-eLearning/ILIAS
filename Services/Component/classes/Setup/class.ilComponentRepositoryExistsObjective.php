<?php

declare(strict_types=1);

use ILIAS\Setup;

class ilComponentRepositoryExistsObjective implements Setup\Objective
{
    /**
     * @inheritdoc
     */
    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return "ilComponentRepository is initialized and stored into the environment.";
    }

    /**
     * @inheritdoc
     */
    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective()
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);

        $data_factory = new ILIAS\Data\Factory();
        $component_repository = new ilArtifactComponentRepository(
            $data_factory,
            new ilPluginStateDBOverIlDBInterface(
                $data_factory,
                $db
            ),
            $data_factory->version(ILIAS_VERSION_NUMERIC)
        );

        return $environment->withResource(
            Setup\Environment::RESOURCE_COMPONENT_REPOSITORY,
            $component_repository
        );
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        return is_null($environment->getResource(Setup\Environment::RESOURCE_COMPONENT_REPOSITORY));
    }
}
