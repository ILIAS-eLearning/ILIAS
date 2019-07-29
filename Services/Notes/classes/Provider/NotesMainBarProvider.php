<?php namespace ILIAS\Notes\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Class NotesMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NotesMainBarProvider extends AbstractStaticMainMenuProvider
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

        // Comments
        $comments = $this->mainmenu->link($this->if->identifier('mm_pd_comments'))
            ->withTitle($dic->language()->txt("mm_comments"))
            ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToComments")
            ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
            ->withPosition(40)
            ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
            ->withAvailableCallable(
                function () use ($dic) {
                    return (bool) (!$dic->settings()->get("disable_comments"));
                }
            );

        // Notes
        $notes = $this->mainmenu->link($this->if->identifier('mm_pd_notes'))
            ->withTitle($dic->language()->txt("mm_notes"))
            ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToNotes")
            ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
            ->withPosition(70)
            ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
            ->withAvailableCallable(
                function () use ($dic) {
                    return (bool) (!$dic->settings()->get("disable_notes"));
                }
            );

        return [
            $comments,
            $notes,
        ];
    }
}
