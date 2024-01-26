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

namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\GlobalScreen\Client\Notifications as ClientNotifications;
use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\NotificationCenter;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\MainControls\Slate\Combined;

/**
 * Class NotificationCenterRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NotificationCenterRenderer extends AbstractMetaBarItemRenderer implements MetaBarItemRenderer
{
    use isSupportedTrait;

    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    private $gs;

    /**
     * @var \ilLanguage
     */
    private $lng;

    /**
     * BaseMetaBarItemRenderer constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->gs = $DIC->globalScreen();
        $this->lng = $DIC->language();
        parent::__construct();
    }

    /**
     * @param NotificationCenter $item
     * @return Component
     */
    protected function getSpecificComponentForItem(isItem $item) : Component
    {
        $f = $this->ui->factory();

        $center = $f->mainControls()->slate()->combined($this->lng->txt("noc"), $this->buildIcon($item))
                    ->withEngaged(false);

        foreach ($this->gs->collector()->notifications()->getNotifications() as $notification) {
            $center = $center->withAdditionalEntry($notification->getRenderer($this->ui->factory())->getNotificationComponentForItem($notification));
        }

        $center = $this->attachJSShowEvent($center);

        return $this->attachJSRerenderEvent($center);
    }

    /**
     * Attaches on load code for communicating back, that the notification
     * center has been opened. This allows to take measures needed to be
     * handled, if the notifications in the center have been consulted.
     * @param Combined $center
     * @return \ILIAS\UI\Component\JavaScriptBindable|Combined
     */
    protected function attachJSShowEvent(Combined $center) : \ILIAS\UI\Component\MainControls\Slate\Combined
    {
        $toggle_signal = $center->getToggleSignal();
        $url = ClientNotifications::NOTIFY_ENDPOINT . "?" . $this->buildShowQuery();

        return $center->withAdditionalOnLoadCode(
            function ($id) use ($toggle_signal, $url) {
                return "
                $(document).on('$toggle_signal', function(event, signalData) {
                    $.ajax({url: '$url'});
                });";
            }
        );
    }

    /**
     * Attaches on load code for re-rendering the notification center. This allows to update the center with asynchronous
     * notifications.
     * @param Combined $center
     * @return \ILIAS\UI\Component\JavaScriptBindable|Combined
     */
    protected function attachJSRerenderEvent(Combined $center) : \ILIAS\UI\Component\MainControls\Slate\Combined
    {
        $url = ClientNotifications::NOTIFY_ENDPOINT . "?" . $this->buildRerenderQuery();

        return $center->withAdditionalOnLoadCode(
            function (string $id) use ($url) : string {
                return "document.addEventListener('rerenderNotificationCenter', () => {
                    let xhr = new XMLHttpRequest();
                    xhr.open('GET', '$url');
                    xhr.onload = () => {
                        if (xhr.status === 200) {
                            let response = JSON.parse(xhr.responseText);
                            $id.querySelector('.il-maincontrols-slate-content').innerHTML = response.html;
                            $id.querySelectorAll('.il-maincontrols-slate-content script').forEach( element => {
                                eval(element.innerHTML);
                            })
                            $id.parentNode.previousElementSibling.querySelector('.glyph').outerHTML = response.symbol;
                        } else {
                            console.error(xhr.status + ': ' + xhr.responseText);
                        }
                    };
                    xhr.send();
                });";
            }
        );
    }

    /**
     * @return string
     */
    protected function buildShowQuery() : string
    {
        return http_build_query([
            ClientNotifications::MODE => ClientNotifications::MODE_OPENED,
            ClientNotifications::NOTIFICATION_IDENTIFIERS => $this->gs->collector()->notifications()->getNotificationsIdentifiersAsArray(true),
        ]);
    }

    protected function buildRerenderQuery() : string
    {
        return http_build_query([ClientNotifications::MODE => ClientNotifications::MODE_RERENDER]);
    }
}
