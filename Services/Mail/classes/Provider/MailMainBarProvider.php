<?php namespace ILIAS\Mail\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ilMailGlobalServices;

/**
 * Class MailMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MailMainBarProvider extends AbstractStaticMainMenuProvider
{

    /**
     * @inheritDoc
     */
    public function getStaticTopItems() : array
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        $dic = $this->dic;

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/envolope-letter.svg"), "");

        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_mail'))
                ->withTitle($this->dic->language()->txt("mm_mail"))
                ->withAction("ilias.php?baseClass=ilMailGUI")
                ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                ->withPosition(10)
                ->withSymbol($icon)
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () use ($dic) {
                        return ($dic->user()->getId() != ANONYMOUS_USER_ID);
                    }
                )
                ->withVisibilityCallable(
                    function () use ($dic) {
                        return $dic->rbac()->system()->checkAccess(
                            'internal_mail', ilMailGlobalServices::getMailObjectRefId()
                        );
                    }
                ),
        ];
    }
}
