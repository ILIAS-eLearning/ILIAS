<?php namespace ILIAS\Mail\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;
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

        $title = $this->dic->language()->txt("mm_mail");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::MAIL, $title)
                                                             ->withIsOutlined(true);

        return [
            $this->mainmenu->complex($this->if->identifier('mm_pd_mail'))
                ->withTitle($title)
                ->withContentWrapper(function () {
                    $exp = new \ilMailExplorer(new \ilMailGUI(), $this->dic->user()->getId());

                    return $this->dic->ui()->factory()->legacy($exp->getHTML(true));
                })
                ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                ->withPosition(10)
                ->withSymbol($icon)
                ->withSupportsAsynchronousLoading(true)
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () use ($dic) {
                        return ($dic->user()->getId() != ANONYMOUS_USER_ID);
                    }
                )
                ->withVisibilityCallable(
                    function () use ($dic) {
                        return $dic->rbac()->system()->checkAccess(
                            'internal_mail',
                            ilMailGlobalServices::getMailObjectRefId()
                        );
                    }
                ),
        ];
    }
}
