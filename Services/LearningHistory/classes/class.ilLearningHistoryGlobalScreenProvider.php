<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;

/**
 * Class ilStaffGlobalScreenProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilLearningHistoryGlobalScreenProvider extends AbstractStaticMainMenuProvider
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

        $dic->language()->loadLanguageModule('lhist');

        return [$this->mainmenu->link($this->if->identifier('mm_pd_lhist'))
                    ->withTitle($this->dic->language()->txt("lhist_learning_history"))
                    ->withAction($dic->ctrl()->getLinkTargetByClass(["ilPersonalDesktopGUI", "ilLearningHistoryGUI"]))
                    ->withParent($this->top_item)
                    ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                    ->withAvailableCallable(
                        function () use ($dic) {
                            return (bool) ($dic->learningHistory()->isActive());
                        }
                    )
                    ->withVisibilityCallable(
                        function () use ($dic) {
                            return (bool) ($dic->learningHistory()->isActive($dic->user()->getId()));
                        }
                    )];
    }
}
