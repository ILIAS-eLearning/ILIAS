<?php declare(strict_types=1);

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
    public function getStaticTopItems() : array
    {
        return [];
    }

    public function getStaticSubItems() : array
    {
        $dic = $this->dic;

        $title = $this->dic->language()->txt("mm_mail");
        $icon = $this->dic->ui()->factory()
            ->symbol()
            ->icon()
            ->standard(Standard::MAIL, $title)
            ->withIsOutlined(true);

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
                    static function () use ($dic) : bool {
                        return !$dic->user()->isAnonymous() && $dic->user()->getId() !== 0;
                    }
                )
                ->withVisibilityCallable(
                    static function () use ($dic) : bool {
                        return $dic->rbac()->system()->checkAccess(
                            'internal_mail',
                            ilMailGlobalServices::getMailObjectRefId()
                        );
                    }
                ),
        ];
    }
}
