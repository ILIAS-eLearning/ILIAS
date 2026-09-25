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

declare(strict_types=1);

use ceLTIc\LTI\Platform;
use ILIAS\DI\Container;
use ILIAS\Filesystem\Stream\Streams;

/**
 * The LTI Advantage launch of an LTI object. The content screen offers it in the way the object is set to
 * open: embedded, in the same window or in a new one. Whichever it is, the launch page starts the OpenID
 * Connect login at the tool, and the tool then gets the id_token from ltiauth.php.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantagePlatformLaunchRenderer
{
    private const string FRAME_ID = 'il_lti_advantage_frame';

    /**
     * @throws ilCtrlException
     */
    public static function renderLaunch(ilObjLTITool $object, ilLTIToolLaunchGUI $gui, Container $dic): void
    {
        $factory = $dic->ui()->factory();
        $url = $dic->ctrl()->getLinkTarget($gui, ilLTIToolLaunchGUI::CMD_START_ADVANTAGE_LAUNCH);

        if ($object->isLaunchMethodEmbedded()) {
            $dic->ui()->mainTemplate()->addOnLoadCode(self::getStorageJS());
            $dic->ui()->mainTemplate()->setContent($dic->ui()->renderer()->render($factory->legacy()->content(
                '<iframe id="' . self::FRAME_ID . '" src="' . htmlspecialchars($url, ENT_QUOTES) . '" title="'
                . htmlspecialchars($object->getTitle(), ENT_QUOTES) . '" width="100%" height="500"></iframe>'
            )));
            return;
        }

        if ($object->getOfflineStatus() || $object->getTool()->getAvailability() === ilLTITool::AVAILABILITY_NONE) {
            return;
        }

        $label = $dic->language()->txt('show_content');
        $dic->toolbar()->addComponent(
            $object->isLaunchMethodOwnWin()
                ? $factory->button()->standard($label, $url)
                : $factory->link()->standard($label, $url)->withOpenInNewViewport(true)
        );
    }

    /**
     * The platform storage the tool in the iframe may keep its state in, from celtic/lti. The library answers
     * every message the page gets, its own answers included, which loops as soon as the page posts a message to
     * itself. It only gets the messages of the iframe of the tool here.
     */
    private static function getStorageJS(): string
    {
        return '(function (window) {' . Platform::getStorageJS() . '})({addEventListener: function (type, listener, options) {'
            . 'window.addEventListener(type, function (event) {'
            . 'var frame = document.getElementById("' . self::FRAME_ID . '");'
            . 'if (frame && event.source === frame.contentWindow) { listener(event); }'
            . '}, options);}});';
    }

    /**
     * Sends the page that starts the launch and ends the request.
     */
    public static function sendLaunchPage(
        ilObjLTITool $object,
        ilCmiXapiUser $cmix_user,
        string $return_url,
        Container $dic
    ): never {
        $status = 200;
        try {
            $page = new ilLTIAdvantagePlatformConnection($object->getTool())->getLaunchPage(
                $object->getRefId(),
                ilLTIAdvantagePlatformLaunchParameterBuilder::build($object, $cmix_user, $return_url),
                $object->isLaunchMethodEmbedded()
            );
        } catch (ilException $e) {
            $dic->logger()->forComponent('lti')->error($e->getMessage());
            $status = 500;
            $page = $dic->language()->txt('error');
        }

        // The tool asks ltiauth.php for the id_token from its own site, often with a POST, which does not carry
        // the session cookie of ILIAS while it is SameSite=Lax. The login the launch starts waits in that
        // session, so the cookie is sent again as SameSite=None, as ilStartUpGUI does for LTI sessions.
        setcookie(session_name(), session_id(), [
            'expires' => 0,
            'path' => rtrim(IL_COOKIE_PATH, '/'),
            'domain' => IL_COOKIE_DOMAIN,
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None',
        ]);

        $dic->http()->saveResponse(
            $dic->http()->response()->withStatus($status)->withBody(Streams::ofString($page))
        );
        $dic->http()->sendResponse();
        $dic->http()->close();
    }
}
