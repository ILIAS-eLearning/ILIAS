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

namespace ILIAS\GlobalScreen\Scope\Toast\Collector\Renderer;

use ILIAS\UI\Component\Component;
use ILIAS\GlobalScreen\Scope\Toast\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Scope\Toast\Factory\isStandardItem;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\GlobalScreen\Client\Notifications as ClientNotifications;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class StandardToastRenderer implements ToastRenderer
{
    use Hasher;

    protected UIServices $ui;

    public function __construct(UIServices $ui)
    {
        $this->ui = $ui;
    }

    public function getToastComponentForItem(isItem $item): Component
    {
        if (!$item instanceof isStandardItem) {
            return $this->ui->factory()->legacy("Cannot render item of type " . get_class($item) . "");
        }

        // build empty UI\Toast
        $toast = $this->ui->factory()->toast()->standard(
            $item->getTitle(),
            $this->getIconOrFallback($item)
        );

        if ($item->getDescription() !== null) {
            $toast = $toast->withDescription($item->getDescription());
        }

        // onClose
        if ($item->hasClosedAction()) {
            $closed_action = $this->buildURL(
                $item->getClosedAction()->getIdentifier(),
                $item->getProviderIdentification()
            );
            $toast = $toast->withAdditionalOnLoadCode(function ($id) use ($closed_action) {
                return "
                $('#$id').on('removeToast', function() {
                    $.ajax({
                        async: false,
                        type: 'GET',
                        url: '$closed_action'
                      });
                });";
            });
        }

        // on Show (there is currently no such event in the UI-Service)
        if ($item->hasShownAction()) {
            $shown_action = $this->buildURL(
                $item->getShownAction()->getIdentifier(),
                $item->getProviderIdentification()
            );
            $toast = $toast->withAdditionalOnLoadCode(function ($id) use ($shown_action) {
                return "
                $('#$id').on('showToast', function() {
                    $.ajax({
                        async: false,
                        type: 'GET',
                        url: '$shown_action'
                      });
                });";
            });
        }

        // onVanish (there is currently no such event in the UI-Service)
        if ($item->hasVanishedAction()) {
            $vanished_action = $this->buildURL(
                $item->getVanishedAction()->getIdentifier(),
                $item->getProviderIdentification()
            );
            $toast = $toast->withAdditionalOnLoadCode(function ($id) use ($vanished_action) {
                return "
                $('#$id').on('vanishToast', function() {
                    $.ajax({
                        async: false,
                        type: 'GET',
                        url: '$vanished_action'
                      });
                });";
            });
        }

        // additional Actions
        foreach ($item->getAdditionalToastActions() as $toast_action) {
            $action = $this->buildURL(
                $toast_action->getIdentifier(),
                $item->getProviderIdentification()
            );
            $link = $this->ui->factory()->link()->standard(
                $toast_action->getTitle(),
                $action
            );

            $toast = $toast->withAdditionalLink($link);
        }

        // Times (currently disbaled since these methods are not on the Interface of a Toast
        if ($item->getVanishTime() !== null) {
            // $toast = $toast->withVanishTime($item->getVanishTime());
        }

        if ($item->getDelayTime() !== null) {
            // $toast = $toast->withDelayTime($item->getDelayTime());
        }

        return $toast;
    }

    private function getIconOrFallback(isStandardItem $item): Icon
    {
        $icon = $item->getIcon();
        if ($icon !== null) {
            return $icon;
        }
        return $this->ui->factory()->symbol()->icon()->standard("nota", $item->getTitle());
    }

    protected function buildURL(string $action, IdentificationInterface $id): string
    {
        $query = http_build_query([
            ClientNotifications::MODE => ClientNotifications::MODE_HANDLE_TOAST_ACTION,
            ClientNotifications::ADDITIONAL_ACTION => $action,
            ClientNotifications::ITEM_ID => $this->hash($id->serialize()),
        ]);

        return rtrim(ILIAS_HTTP_PATH, "/") . "/" . ClientNotifications::NOTIFY_ENDPOINT . "?" . $query;
    }
}
