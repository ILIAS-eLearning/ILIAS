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
use ILIAS\HTTP\Response\Sender\ResponseSendingException;
use ILIAS\UI\Component\Component;
use Random\RandomException;

/**
 * The LTI Advantage launch of an LTI object. The content screen offers it in the way the object is set to
 * open: embedded, in the same window or in a new one. Whichever it is, the launch page starts the OpenID
 * Connect login at the tool, and the tool then gets the id_token from ltiauth.php.
 *
 * An object whose content was picked by Deep Linking launches the target link the tool gave it, kept as its
 * custom parameter target_link_uri as in earlier releases.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantagePlatformLaunchRenderer
{
    private const string FRAME_ID = 'il_lti_advantage_frame';
    private const string TARGET_LINK_PARAM = 'target_link_uri';

    /**
     * @throws ilCtrlException
     */
    public static function renderLaunch(ilObjLTITool $object, ilLTIToolLaunchGUI $gui, Container $dic): void
    {
        $factory = $dic->ui()->factory();
        $url = $dic->ctrl()->getLinkTarget($gui, ilLTIToolLaunchGUI::CMD_START_ADVANTAGE_LAUNCH);

        if ($object->isLaunchMethodEmbedded()) {
            $dic->ui()->mainTemplate()->setContent($dic->ui()->renderer()->render(
                self::buildFrame($url, $object->getTitle(), 500, $dic)
            ));
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
     * The iframe a tool opens in, with the platform storage of LTI for it.
     */
    public static function buildFrame(string $url, string $title, int $height, Container $dic): Component
    {
        $dic->ui()->mainTemplate()->addOnLoadCode(self::getStorageJS());

        return self::buildIframe(self::FRAME_ID, $url, $title, $height, $dic);
    }

    /**
     * An iframe of a page of the other side of LTI Advantage, which the page around it tells apart by its id.
     */
    public static function buildIframe(string $id, string $url, string $title, int $height, Container $dic): Component
    {
        return $dic->ui()->factory()->legacy()->content(
            '<iframe id="' . htmlspecialchars($id, ENT_QUOTES) . '" src="' . htmlspecialchars($url, ENT_QUOTES)
            . '" title="' . htmlspecialchars($title, ENT_QUOTES) . '" width="100%" height="' . $height . '"></iframe>'
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
     *
     * @throws RandomException
     * @throws ResponseSendingException
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
                $object->isLaunchMethodEmbedded(),
                (string) ($object->getCustomParamsArray()[self::TARGET_LINK_PARAM] ?? '')
            );
        } catch (ilException $e) {
            $dic->logger()->forComponent('lti')->error($e->getMessage());
            $status = 500;
            $page = $dic->language()->txt('error');
        }

        self::sendPage($page, $status);
    }

    /**
     * Sends a page that starts a message to the tool and ends the request.
     *
     */
    public static function sendPage(string $page, int $status): never
    {
        // The tool asks ltiauth.php for the id_token from its own site, often with a POST, which does not carry
        // the session cookie of ILIAS while it is SameSite=Lax. The login the launch starts waits in that
        // session, so the cookie is sent again as SameSite=None, as ilStartUpGUI does for LTI sessions.
        ilLTISessionCookie::allowCrossSite();

        ilLTIAdvantageResponse::html($page, $status);
    }
}
