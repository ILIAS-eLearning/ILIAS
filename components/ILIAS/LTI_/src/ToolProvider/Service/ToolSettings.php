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

use ILIAS\LTI\ToolProvider\Platform;
use ILIAS\LTI\ToolProvider\Context;
use ILIAS\LTI\ToolProvider\ResourceLink;

/**
 * Class to implement the Tool Settings service
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class ToolSettings extends Service
{
    /**
     * Settings at current level mode.
     */
    public const MODE_CURRENT_LEVEL = 1;

    /**
     * Settings at all levels mode.
     */
    public const MODE_ALL_LEVELS = 2;

    /**
     * Settings with distinct names at all levels mode.
     */
    public const MODE_DISTINCT_NAMES = 3;

    /**
     * Access scope.
     */
    public static string $SCOPE = 'https://purl.imsglobal.org/spec/lti-ts/scope/toolsetting';

    /**
     * Names of LTI parameters to be retained in the consumer settings property.
     *
     * @var array $LEVEL_NAMES
     */
    private static array $LEVEL_NAMES = array('ToolProxy' => 'system',
                                              'ToolProxyBinding' => 'context',
                                              'LtiLink' => 'link');

    /**
     * The object to which the settings apply (ResourceLink, Context or Platform).
     *
     * @var Platform|Context|ResourceLink  $source
     */
    private $source;

    /**
     * Whether to use the simple JSON format.
     *
     * @var bool $simple
     */
    private bool $simple;

    /**
     * Class constructor.
     * @param Platform|Context|ResourceLink $source   The object to which the settings apply (ResourceLink, Context or Platform)
     * @param string                        $endpoint Service endpoint
     * @param bool                          $simple   True if the simple media type is to be used (optional, default is true)
     */
    public function __construct($source, string $endpoint, bool $simple = true)
    {
        if (is_a($source, 'ceLTIc\LTI\Platform')) {
            $platform = $source;
        } else {
            $platform = $source->getPlatform();
        }
        parent::__construct($platform, $endpoint);
        $this->scope = self::$SCOPE;
        if ($simple) {
            $this->mediaType = 'application/vnd.ims.lti.v2.toolsettings.simple+json';
        } else {
            $this->mediaType = 'application/vnd.ims.lti.v2.toolsettings+json';
        }
        $this->source = $source;
        $this->simple = $simple;
    }

    /**
     * Get the tool settings.
     * @param int $mode Mode for request (optional, default is current level only)
     * @return mixed The array of settings if successful, otherwise false
     */
    public function get(int $mode = self::MODE_CURRENT_LEVEL)
    {
        $parameter = array();
        if ($mode === self::MODE_ALL_LEVELS) {
            $parameter['bubble'] = 'all';
        } elseif ($mode === self::MODE_DISTINCT_NAMES) {
            $parameter['bubble'] = 'distinct';
        }
        $http = $this->send('GET', $parameter);
        if (!$http->ok) {
            $response = false;
        } elseif ($this->simple) {
            $response = json_decode($http->response, true);
        } elseif (isset($http->responseJson->{'@graph'})) {
            $response = array();
            foreach ($http->responseJson->{'@graph'} as $level) {
                $settings = json_decode(json_encode($level->custom), true);
                unset($settings['@id']);
                $response[self::$LEVEL_NAMES[$level->{'@type'}]] = $settings;
            }
        }

        return $response;
    }

    /**
     * Set the tool settings.
     * @param array $settings An associative array of settings (optional, default is null)
     * @return bool True if request was successful
     */
    public function set(array $settings): bool
    {
        if (!$this->simple) {
            if (is_a($this->source, 'Platform')) {
                $type = 'ToolProxy';
            } elseif (is_a($this->source, 'Context')) {
                $type = 'ToolProxyBinding';
            } else {
                $type = 'LtiLink';
            }
            $obj = new \stdClass();
            $obj->{'@context'} = 'http://purl.imsglobal.org/ctx/lti/v2/ToolSettings';
            $obj->{'@graph'} = array();
            $level = new \stdClass();
            $level->{'@type'} = $type;
            $level->{'@id'} = $this->endpoint;
            $level->{'custom'} = $settings;
            $obj->{'@graph'}[] = $level;
            $body = json_encode($obj);
        } else {
            $body = json_encode($settings);
        }

        $response = parent::send('PUT', null, $body);

        return $response->ok;
    }
}
