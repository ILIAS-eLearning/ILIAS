<?php namespace ILIAS\PersonalWorkspace\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Class WorkspaceMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class WorkspaceMainBarProvider extends AbstractStaticMainMenuProvider
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

        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("fold", "")->withIsOutlined(true);
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/folder-alt.svg"), "");

        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_wsp'))
                ->withTitle($this->dic->language()->txt("mm_personal_and_shared_r"))
                ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToWorkspace")
                ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
                ->withPosition(60)
	            ->withSymbol($icon)
	            ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () use ($dic) {
                        return (bool) (!$dic->settings()->get("disable_personal_workspace"));
                    }
                ),
        ];
    }
}
