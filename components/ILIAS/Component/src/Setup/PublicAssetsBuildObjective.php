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

namespace ILIAS\Component\Setup;

use ILIAS\Setup;
use ILIAS\Component\Resource\PublicAssetManager;

class PublicAssetsBuildObjective implements Setup\Objective
{
    public function __construct(
        protected PublicAssetManager $public_asset_manager,
        protected array $public_assets
    ) {
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "The public folder is populated with all required assets.";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $base_dir = realpath(__DIR__ . "/../../../../../");
        $target = realpath($base_dir) . "/public";

        $this->public_asset_manager->addAssets(...$this->public_assets);
        $this->public_asset_manager->buildPublicFolder($base_dir, $target);

        return $environment;
    }

    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }

}
