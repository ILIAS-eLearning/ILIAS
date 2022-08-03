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

use ILIAS\LTI\ToolProvider\Service;

/**
 * Class to implement resource link services for a platform via its proprietary API
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class ApiResourceLink
{

    /**
     * Resource link object.
     *
     * @var \ILIAS\LTI\ToolProvider\ResourceLink|null $resourceLink //changed from: \ceLTIc\LTI\ResourceLink
     */
    protected ?\ILIAS\LTI\ToolProvider\ResourceLink $resourceLink = null;

    /**
     * Class constructor.
     * @param \ILIAS\LTI\ToolProvider\ResourceLink $resourceLink //changed from: \ceLTIc\LTI\ResourceLink
     */
    public function __construct(\ILIAS\LTI\ToolProvider\ResourceLink $resourceLink)
    {
        $this->resourceLink = $resourceLink;
    }

    /**
     * Check if the API hook has been configured.
     */
    public function isConfigured() : bool
    {
        return true;
    }

    /**
     * Perform an Outcomes service request.
     * @param int        $action     The action type constant
     * @param \ILIAS\LTI\ToolProvider\Outcome    $ltiOutcome Outcome object
     * @param \ILIAS\LTI\ToolProvider\UserResult $userresult UserResult object
     * @return string|bool    Outcome value read or true if the request was successfully processed
     */
    public function doOutcomesService(int $action, \ILIAS\LTI\ToolProvider\Outcome $ltiOutcome, \ILIAS\LTI\ToolProvider\UserResult $userresult)
    {
        return false;
    }

    /**
     * Get memberships.
     * @param bool $withGroups True is group information is to be requested as well
     * @return mixed Array of UserResult objects or False if the request was not successful
     */
    public function getMemberships(bool $withGroups)
    {
        return false;
    }

    /**
     * Get Tool Settings.
     * @param int  $mode   Mode for request (optional, default is current level only)
     * @param bool $simple True if all the simple media type is to be used (optional, default is true)
     * @return mixed The array of settings if successful, otherwise false
     */
    public function getToolSettings(int $mode = Service\ToolSettings::MODE_CURRENT_LEVEL, bool $simple = true)
    {
        return false;
    }

    /**
     * Perform a Tool Settings service request.
     * @param array $settings An associative array of settings (optional, default is none)
     * @return bool    True if action was successful, otherwise false
     */
    public function setToolSettings(array $settings = array()) : bool
    {
        return false;
    }
}
