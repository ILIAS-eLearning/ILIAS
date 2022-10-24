<?php

declare(strict_types=1);

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

use ILIAS\Setup;

/**
 * The user seems to use `root` or we cannot determine which user he uses.
 * We should ask...
 */
class ilUseRootConfirmed implements Setup\Objective
{
    /**
     * @inheritdoc
     */
    public function getHash(): string
    {
        return hash(
            "sha256",
            get_class($this)
        );
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return "Confirm that root should be used to run the setup.";
    }

    /**
     * @inheritdoc
     */
    public function isNotable(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $admin_interaction = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);

        $message =
            "You seem to be using root or your user just can't be determined. You should\n" .
            "be running this setup with the same user the webserver uses. If this is not\n" .
            "the case there might be problems accessing files via the web later...\n";

        if (!$admin_interaction->confirmOrDeny($message)) {
            throw new Setup\NoConfirmationException($message);
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        if (function_exists("posix_geteuid") && posix_geteuid() != 0) {
            return false;
        }

        return true;
    }
}
