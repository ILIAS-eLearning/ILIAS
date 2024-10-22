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

namespace ILIAS\LTI\ToolProvider\Service;

/**
 * Class to implement the Assignment and Grade services
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class AssignmentGrade extends Service
{
    /**
     * Class constructor.
     * @param \ILIAS\LTI\ToolProvider\Platform $platform Platform object for this service request
     * @param string   $endpoint Service endpoint
     * @param string   $path     Path (optional)
     */
    public function __construct(\ILIAS\LTI\ToolProvider\Platform $platform, string $endpoint, string $path = '')
    {
        $endpoint = self::addPath($endpoint, $path);
        parent::__construct($platform, $endpoint);
    }

    /**
     * Add path to a URL.
     * @param string $endpoint Service endpoint
     * @param string $path     Path
     * @return string The endpoint with the path added
     */
    private static function addPath(string $endpoint, string $path): string
    {
        if (strpos($endpoint, '?') === false) {
            if (substr($endpoint, -strlen($path)) !== $path) {
                $endpoint .= $path;
            }
        } elseif (strpos($endpoint, "{$path}?") === false) {
            $endpoint = str_replace('?', "{$path}?", $endpoint);
        }

        return $endpoint;
    }
}
