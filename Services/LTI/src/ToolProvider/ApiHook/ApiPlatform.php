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
 * Class to implement services for a platform via its proprietary API
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class ApiPlatform
{
    /**
     * Platform object.
     *
     * @var \ILIAS\LTI\ToolProvider\Platform|null $platform //UK: changed from \ceLTIc\LTI\Platform
     */
    protected ?\ILIAS\LTI\ToolProvider\Platform $platform = null;

    /**
     * Class constructor.
     * @param \ILIAS\LTI\ToolProvider\Platform $platform //UK: changed from \ceLTIc\LTI\Platform
     */
    public function __construct(\ILIAS\LTI\ToolProvider\Platform $platform)
    {
        $this->platform = $platform;
    }

    /**
     * Check if the API hook has been configured.
     */
    public function isConfigured(): bool
    {
        return true;
    }

    /**
     * Get Tool Settings.
     * @param bool $simple True if all the simple media type is to be used (optional, default is true)
     * @return mixed The array of settings if successful, otherwise false
     */
    public function getToolSettings(bool $simple = true)
    {
        return false;
    }

    /**
     * Perform a Tool Settings service request.
     * @param array $settings An associative array of settings (optional, default is none)
     * @return bool    True if action was successful, otherwise false
     */
    public function setToolSettings(array $settings = array()): bool
    {
        return false;
    }
}
