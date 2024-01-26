<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
    public function getHash() : string
    {
        return hash(
            "sha256",
            get_class($this)
        );
    }

    /**
     * @inheritdoc
     */
    public function getLabel() : string
    {
        return "Confirm that root should be used to run the setup.";
    }

    /**
     * @inheritdoc
     */
    public function isNotable() : bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $admin_interaction = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);

        $message =
            "You seem to be using root or your user just can't be determined. You should\n" .
            "be running this setup with the same user the webserver uses. If this is not\n" .
            "the case there might be problems accessing files via the web later...\n".
            "If you still proceed, carefully check file access rights in the data-directories\n".
            "after finishing the setup.\n";

        if (!$admin_interaction->confirmOrDeny($message)) {
            throw new Setup\NoConfirmationException($message);
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        if (function_exists("posix_geteuid") && posix_geteuid() != 0) {
            return false;
        }

        return true;
    }
}
