<?php

namespace ILIAS\LTI\ToolProvider\MediaType;

use ILIAS\LTI\ToolProvider\Tool;
use ILIAS\LTI\ToolProvider\Profile;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class to represent an LTI Resource Handler
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class ResourceHandler
{

    /**
     * Class constructor.
     * @param Tool                    $tool            Tool object
     * @param Profile\ResourceHandler $resourceHandler Profile resource handler object
     */
    public function __construct(Tool $tool, Profile\ResourceHandler $resourceHandler)
    {
        $this->resource_type = new \stdClass;
        $this->resource_type->code = $resourceHandler->item->id;
        $this->resource_name = new \stdClass;
        $this->resource_name->default_value = $resourceHandler->item->name;
        $this->resource_name->key = "{$resourceHandler->item->id}.resource.name";
        $this->description = new \stdClass;
        $this->description->default_value = $resourceHandler->item->description;
        $this->description->key = "{$resourceHandler->item->id}.resource.description";
        $icon_info = new \stdClass;
        $icon_info->default_location = new \stdClass;
        $icon_info->default_location->path = $resourceHandler->icon;
        $icon_info->key = "{$resourceHandler->item->id}.icon.path";
        $this->icon_info = array();
        $this->icon_info[] = $icon_info;
        $this->message = array();
        foreach ($resourceHandler->requiredMessages as $message) {
            $this->message[] = new Message($message, $tool->platform->profile->capability_offered);
        }
        foreach ($resourceHandler->optionalMessages as $message) {
            if (in_array($message->type, $tool->platform->profile->capability_offered)) {
                $this->message[] = new Message($message, $tool->platform->profile->capability_offered);
            }
        }
    }
}
