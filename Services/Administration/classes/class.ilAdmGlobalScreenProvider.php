<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;

/**
 * Class ilAdmGlobalScreenProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilAdmGlobalScreenProvider extends AbstractStaticMainMenuProvider
{

    /**
     * @var IdentificationInterface
     */
    protected $top_item;


    public function __construct(\ILIAS\DI\Container $dic)
    {
        parent::__construct($dic);
        $this->top_item = $this->if->identifier('adm');
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
        $dic = $this->dic;

        return [$this->mainmenu->topParentItem($this->getTopItem())
                    ->withTitle($this->dic->language()->txt("administration"))
                    ->withPosition(3)
                    ->withVisibilityCallable(
                        function () use ($dic) {
                            return (bool) ($dic->access()->checkAccess('visible', '', SYSTEM_FOLDER_ID));
                        }
                    )];
    }


    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        $dic = $this->dic;

        $entries[] = $this->globalScreen()
            ->mainmenu()
            ->complex($this->if->identifier('adm_content'))
            ->withAsyncContentURL("ilias.php?baseClass=ilAdministrationGUI&cmd=getDropDown&cmdMode=asynch")
            ->withParent($this->getTopItem())
            ->withAlwaysAvailable(true)
            ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('item_must_be_always_active')}"))
            ->withVisibilityCallable(
                function () use ($dic) {
                    return (bool) ($dic->rbac()->system()->checkAccess("visible", SYSTEM_FOLDER_ID));
                }
            )->withAvailableCallable(
                function () use ($dic) {
                    return ($dic->user()->getId() != ANONYMOUS_USER_ID);
                }
            );

        return $entries;
    }
}
