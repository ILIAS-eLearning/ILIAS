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
        $ctrl = $dic->ctrl();

        // Comments
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/speech.svg"), "");
        $comments = $this->mainmenu->link($this->if->identifier('mm_pd_comments'))
            ->withTitle($dic->language()->txt("mm_comments"))
            ->withAction($ctrl->getLinkTargetByClass(["ilPersonalDesktopGUI", "ilPDNotesGUI"], "showPublicComments"))
            ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
            ->withPosition(40)
            ->withSymbol($icon)
            ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
            ->withAvailableCallable(
                function () use ($dic) {
                    return (bool) (!$dic->settings()->get("disable_comments"));
                }
            );

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/note.svg"), "");

        // Notes
        $notes = $this->mainmenu->link($this->if->identifier('mm_pd_notes'))
            ->withTitle($dic->language()->txt("mm_notes"))
            ->withAction($ctrl->getLinkTargetByClass(["ilPersonalDesktopGUI", "ilPDNotesGUI"], "showPrivateNotes"))
            ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
            ->withPosition(70)
            ->withSymbol($icon)
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
