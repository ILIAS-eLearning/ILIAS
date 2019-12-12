<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;

/**
 * Class ilSkillGlobalScreenProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilSkillGlobalScreenProvider extends AbstractStaticMainMenuProvider
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
        return [];
        /*
        return [$this->mainmenu->link($this->if->identifier('mm_pd_skill'))
                    ->withTitle($this->dic->language()->txt("skills"))
                    ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSkills")
                    ->withParent($this->getTopItem())
                    ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                    ->withAvailableCallable(
                        function () {
                            $skmg_set = new ilSetting("skmg");

                            return (bool)($skmg_set->get("enable_skmg"));
                        }
                    )];*/
    }
}
