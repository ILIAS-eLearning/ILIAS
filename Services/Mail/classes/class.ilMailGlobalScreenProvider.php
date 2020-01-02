<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;

/**
 * Class ilMailGlobalScreenProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMailGlobalScreenProvider extends AbstractStaticMainMenuProvider
{

    /**
     * @var IdentificationInterface
     */
    protected $top_item;


    public function __construct(\ILIAS\DI\Container $dic)
    {
        parent::__construct($dic);
        $this->top_item = (new ilPDGlobalScreenProvider($dic))->getTopItem();
    }


    /**
     * Some other components want to provide Items for the main menu which are
     * located at the PD TopTitem by default. Therefore we have to provide our
     * TopTitem Identification for others
     *
     * @return IdentificationInterface
     */
    public function getTopItem() : IdentificationInterface
    {
        return $this->top_item;
    }


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

        return [$this->mainmenu->link($this->if->identifier('mm_pd_mail'))
                    ->withTitle($this->dic->language()->txt("mail"))
                    ->withAction("ilias.php?baseClass=ilMailGUI")
                    ->withParent($this->getTopItem())
                    ->withPosition(8)
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
