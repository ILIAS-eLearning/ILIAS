<?php

declare(strict_types=1);

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

namespace ILIAS\Mail\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ilMailGlobalServices;

/**
 * Class MailMainBarProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MailMainBarProvider extends AbstractStaticMainMenuProvider
{
    public function getStaticTopItems(): array
    {
        return [];
    }

    public function getStaticSubItems(): array
    {
        $dic = $this->dic;

        $title = $this->dic->language()->txt("mm_mail");
        $icon = $this->dic->ui()->factory()
            ->symbol()
            ->icon()
            ->standard(Standard::MAIL, $title);

        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_mail'))
                ->withTitle($title)
                ->withAction('ilias.php?baseClass=ilMailGUI')
                ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                ->withPosition(10)
                ->withSymbol($icon)
                ->withNonAvailableReason(
                    $this->dic->ui()->factory()->legacy($this->dic->language()->txt('component_not_active'))
                )
                ->withAvailableCallable(
                    static function () use ($dic): bool {
                        return !$dic->user()->isAnonymous() && $dic->user()->getId() !== 0;
                    }
                )
                ->withVisibilityCallable(
                    static function () use ($dic): bool {
                        return $dic->rbac()->system()->checkAccess(
                            'internal_mail',
                            ilMailGlobalServices::getMailObjectRefId()
                        );
                    }
                ),
        ];
    }
}
