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

use ILIAS\LTI\ToolProvider\Tool;

/**
 * Class to represent an LTI Security Contract document
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class SecurityContract
{
    /**
     * Class constructor.
     * @param Tool   $tool   Tool instance
     * @param string $secret Shared secret
     */
    public function __construct(Tool $tool, string $secret)
    {
        $tcContexts = array();
        foreach ($tool->platform->profile->{'@context'} as $context) {
            if (is_object($context)) {
                $tcContexts = array_merge(get_object_vars($context), $tcContexts);
            }
        }

        $this->shared_secret = $secret;
        $toolServices = array();
        foreach ($tool->requiredServices as $requiredService) {
            foreach ($requiredService->formats as $format) {
                $service = $tool->findService($format, $requiredService->actions);
                if (($service !== false) && !array_key_exists($service->{'@id'}, $toolServices)) {
                    $id = $service->{'@id'};
                    $parts = explode(':', $id, 2);
                    if (count($parts) > 1) {
                        if (array_key_exists($parts[0], $tcContexts)) {
                            $id = "{$tcContexts[$parts[0]]}{$parts[1]}";
                        }
                    }
                    $toolService = new \stdClass();
                    $toolService->{'@type'} = 'RestServiceProfile';
                    $toolService->service = $id;
                    $toolService->action = $requiredService->actions;
                    $toolServices[$service->{'@id'}] = $toolService;
                }
            }
        }
        foreach ($tool->optionalServices as $optionalService) {
            foreach ($optionalService->formats as $format) {
                $service = $tool->findService($format, $optionalService->actions);
                if (($service !== false) && !array_key_exists($service->{'@id'}, $toolServices)) {
                    $id = $service->{'@id'};
                    $parts = explode(':', $id, 2);
                    if (count($parts) > 1) {
                        if (array_key_exists($parts[0], $tcContexts)) {
                            $id = "{$tcContexts[$parts[0]]}{$parts[1]}";
                        }
                    }
                    $toolService = new \stdClass();
                    $toolService->{'@type'} = 'RestServiceProfile';
                    $toolService->service = $id;
                    $toolService->action = $optionalService->actions;
                    $toolServices[$service->{'@id'}] = $toolService;
                }
            }
        }
        $this->tool_service = array_values($toolServices);
    }
}
