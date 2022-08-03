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


namespace ILIAS\LTI\ToolProvider\Profile;

/**
 * Class to represent a resource handler object
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class ResourceHandler
{

    /**
     * General details of resource handler.
     *
     * @var Item|null $item
     */
    public ?Item $item = null;

    /**
     * URL of icon.
     *
     * @var string|null $icon
     */
    public ?string $icon = null;

    /**
     * Required Message objects for resource handler.
     *
     * @var array|null $requiredMessages
     */
    public ?array $requiredMessages = null;

    /**
     * Optional Message objects for resource handler.
     *
     * @var array|null $optionalMessages
     */
    public ?array $optionalMessages = null;

    /**
     * Class constructor.
     * @param Item   $item             General details of resource handler
     * @param string $icon             URL of icon
     * @param array  $requiredMessages Array of required Message objects for resource handler
     * @param array  $optionalMessages Array of optional Message objects for resource handler
     */
    public function __construct(Item $item, string $icon, array $requiredMessages, array $optionalMessages)
    {
        $this->item = $item;
        $this->icon = $icon;
        $this->requiredMessages = $requiredMessages;
        $this->optionalMessages = $optionalMessages;
    }
}
