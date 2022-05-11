<?php

namespace ILIAS\LTI\ToolProvider\MediaType;

use ILIAS\LTI\ToolProvider\Tool;
use ILIAS\LTI\ToolProvider\Profile\ServiceDefinition;

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
 * Class to represent an LTI Tool Proxy media type
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class ToolProxy
{

    /**
     * Class constructor.
     * @param Tool              $tool             Tool  object
     * @param ServiceDefinition $toolProxyService Tool Proxy service
     * @param string            $secret           Shared secret
     */
    public function __construct(Tool $tool, ServiceDefinition $toolProxyService, string $secret)
    {
        $contexts = array();

        $this->{'@context'} = array_merge(array('http://purl.imsglobal.org/ctx/lti/v2/ToolProxy'), $contexts);
        $this->{'@type'} = 'ToolProxy';
        $this->{'@id'} = "{$toolProxyService->endpoint}";
        $this->lti_version = 'LTI-2p0';
        $this->tool_consumer_profile = $tool->platform->profile->{'@id'};
        $this->tool_profile = new ToolProfile($tool);
        $this->security_contract = new SecurityContract($tool, $secret);
    }
}
