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

namespace ILIAS\Awareness\User;

use ILIAS\DI\Container;

/**
 * Awareness providers are
 * @author Alexander Killing <killing@leifos.de>
 */
class ProviderFactory
{
    /**
     * @var \string[][]
     */
    protected static array $providers = array(
        array(
            "component" => "Services/Contact/BuddySystem",
            "class" => "ilAwarenessUserProviderContactRequests"
        ),
        array(
            "component" => "Services/Awareness",
            "class" => "\ILIAS\Awareness\User\ProviderSystemContacts"
        ),
        array(
            "component" => "Services/Awareness",
            "class" => "\ILIAS\Awareness\User\ProviderCourseContacts"
        ),
        array(
            "component" => "Services/Awareness",
            "class" => "\ILIAS\Awareness\User\ProviderCurrentCourse"
        ),
        array(
            "component" => "Services/Contact/BuddySystem",
            "class" => "ilAwarenessUserProviderApprovedContacts"
        ),
        array(
            "component" => "Services/Awareness",
            "class" => "\ILIAS\Awareness\User\ProviderMemberships"
        ),
        array(
            "component" => "Services/Awareness",
            "class" => "\ILIAS\Awareness\User\ProviderAllUsers"
        )
    );
    protected Container $dic;

    public function __construct(Container $DIC)
    {
        $this->dic = $DIC;
    }

    /**
     * Get all awareness providers
     * @return Provider[] array of ilAwarenessProvider all providers
     */
    public function getAllProviders() : array
    {
        $providers = array();

        foreach (self::$providers as $p) {
            $providers[] = new $p["class"]($this->dic);
        }
    
        return $providers;
    }
}
