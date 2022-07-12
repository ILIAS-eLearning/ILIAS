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
 *********************************************************************/
 
namespace ILIAS\Setup\Objective;

use ILIAS\Setup;

/**
 * An admin needs to confirm something to achieve this objective.
 */
class AdminConfirmedObjective implements Setup\Objective
{
    protected string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * @inheritdoc
     */
    public function getHash() : string
    {
        return hash(
            "sha256",
            get_class($this) . "::" . $this->message
        );
    }

    /**
     * @inheritdoc
     */
    public function getLabel() : string
    {
        return "Get a confirmation from admin.";
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

        if (!$admin_interaction->confirmOrDeny($this->message)) {
            throw new Setup\NoConfirmationException(
                $this->message
            );
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return true;
    }
}
