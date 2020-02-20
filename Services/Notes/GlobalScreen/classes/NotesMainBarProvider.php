<?php namespace ILIAS\Notes\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;

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
        $title = $dic->language()->txt("mm_comments");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::COMS, $title)->withIsOutlined(true);
        $comments = $this->mainmenu->link($this->if->identifier('mm_pd_comments'))
            ->withTitle($title)
            ->withAction($ctrl->getLinkTargetByClass(["ilDashboardGUI", "ilPDNotesGUI"], "showPublicComments"))
            ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
            ->withPosition(40)
            ->withSymbol($icon)
            ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
            ->withAvailableCallable(
                function () use ($dic) {
                    return (bool) (!$dic->settings()->get("disable_comments"));
                }
            );

        $title = $dic->language()->txt("mm_notes");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::NOTS, $title)->withIsOutlined(true);

        // Notes
        $notes = $this->mainmenu->link($this->if->identifier('mm_pd_notes'))
            ->withTitle($title)
            ->withAction($ctrl->getLinkTargetByClass(["ilDashboardGUI", "ilPDNotesGUI"], "showPrivateNotes"))
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
