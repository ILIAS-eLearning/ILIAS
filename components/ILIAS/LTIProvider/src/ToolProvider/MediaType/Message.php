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

namespace ILIAS\LTI\ToolProvider\MediaType;

/**
 * Class to represent an LTI Message
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class Message
{
    /**
     * Class constructor.
     * @param \ILIAS\LTI\ToolProvider\Profile\Message $message             Message object  //UK: changed from Message to \ILIAS\LTI\ToolProvider\Profile\Message
     * @param array   $capabilitiesOffered Capabilities offered
     */
    public function __construct(\ILIAS\LTI\ToolProvider\Profile\Message $message, array $capabilitiesOffered)
    {
        $this->message_type = $message->type;
        $this->path = $message->path;
        $this->enabled_capability = array();
        foreach ($message->capabilities as $capability) {
            if (in_array($capability, $capabilitiesOffered)) {
                $this->enabled_capability[] = $capability;
            }
        }
        $this->parameter = array();
        foreach ($message->constants as $name => $value) {
            $parameter = new \stdClass();
            $parameter->name = $name;
            $parameter->fixed = $value;
            $this->parameter[] = $parameter;
        }
        foreach ($message->variables as $name => $value) {
            if (in_array($value, $capabilitiesOffered)) {
                $parameter = new \stdClass();
                $parameter->name = $name;
                $parameter->variable = $value;
                $this->parameter[] = $parameter;
            }
        }
    }
}
