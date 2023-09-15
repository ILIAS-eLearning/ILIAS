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

namespace ILIAS\LTI\ToolProvider\ApiHook;

/**
 * Class to implement tool specific functions for LTI messages
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class ApiTool
{
    /**
     * Tool object.
     *
     * @var \ILIAS\LTI\ToolProvider\Tool|null $tool //UK: changed from \ceLTIc\LTI\Tool
     */
    protected ?\ILIAS\LTI\ToolProvider\Tool $tool = null;

    /**
     * Class constructor.
     * @param \ILIAS\LTI\ToolProvider\Tool|null $tool //UK: changed from \ceLTIc\LTI\Tool
     */
    public function __construct(?\ILIAS\LTI\ToolProvider\Tool $tool)
    {
        $this->tool = $tool;
    }

    /**
     * Check if the API hook has been configured.
     */
    public function isConfigured(): bool
    {
        return true;
    }

    /**
     * Get the User ID.
     *
     * @return string User ID value, or empty string if not available.
     */
    public function getUserId(): string
    {
        return '';
    }

    /**
     * Get the Context ID.
     *
     * @return string Context ID value, or empty string if not available.
     */
    public function getContextId(): string
    {
        return '';
    }
}
