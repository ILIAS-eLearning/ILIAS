<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\UI;
use ILIAS\Glossary\Presentation;
use ILIAS\Glossary\Flashcard;

/**
 * GUI class for glossary flashcards
 * @author Thomas Famula <famula@leifos.de>
 * @ilCtrl_Calls ilGlossaryFlashcardGUI: ilGlossaryFlashcardBoxGUI
 */
class ilGlossaryFlashcardGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs_gui;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected Presentation\PresentationGUIRequest $request;
    protected Flashcard\FlashcardManager $manager;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs_gui = $DIC->tabs();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();

        $this->request = $DIC->glossary()
                             ->internal()
                             ->gui()
                             ->presentation()
                             ->request();
        $gs = $DIC->glossary()->internal();
        $this->manager = $gs->domain()->flashcard($this->request->getRefId());
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case "ilglossaryflashcardboxgui":
                $flash_boxes = new ilGlossaryFlashcardBoxGUI();
                $this->ctrl->forwardCommand($flash_boxes);
                break;

            default:
                $cmd = $this->ctrl->getCmd("listBoxes");
                $ret = $this->$cmd();
                break;
        }
    }

    public function listBoxes(): void
    {
        $flashcard_tpl = new ilTemplate("tpl.flashcard_overview.html", true, true, "Modules/Glossary");

        $reset_btn = $this->ui_fac->button()->standard(
            $this->lng->txt("glo_reset_all_boxes"),
            $this->ctrl->getLinkTarget($this, "confirmResetBoxes")
        );
        $flashcard_tpl->setVariable("RESET_BUTTON", $this->ui_ren->render($reset_btn));

        $intro_box = $this->ui_fac->panel()->standard(
            $this->lng->txt("glo_introduction"),
            $this->ui_fac->legacy($this->lng->txt("glo_flashcards_intro"))
        );
        $flashcard_tpl->setVariable("INTRO_BOX", $this->ui_ren->render($intro_box));

        $boxes = [];
        for ($b = 1; $b <= 5; $b++) {
            $box = $this->getItemBox($b);
            $boxes[] = $box;
        }

        $boxes_pnl = $this->ui_fac->panel()->listing()->standard(
            $this->lng->txt("glo_boxes"),
            [$this->ui_fac->item()->group("", $boxes)]
        );
        $flashcard_tpl->setVariable("BOXES", $this->ui_ren->render($boxes_pnl));

        $this->tpl->setContent($flashcard_tpl->get());
    }

    protected function getItemBox(int $nr): \ILIAS\UI\Component\Item\Item
    {
        $item_cnt = $this->manager->getItemsForBoxCount($nr);
        $last_access = $this->manager->getLastAccessForBoxInDaysText($nr);

        if (($this->manager->getUserTermIdsForBox($nr) && $nr !== Flashcard\FlashcardBox::LAST_BOX)
            || ($this->manager->getAllTermsWithoutEntry() && $nr === Flashcard\FlashcardBox::FIRST_BOX)) {
            $this->ctrl->setParameterByClass("ilglossaryflashcardboxgui", "box_id", $nr);
            $title = $this->ui_fac->link()->standard(
                $this->lng->txt("glo_box") . " " . $nr,
                $this->ctrl->getLinkTargetByClass('ilglossaryflashcardboxgui', 'show')
            );
        } else {
            $title = $this->lng->txt("glo_box") . " " . $nr;
        }

        $box = $this->ui_fac->item()->standard($title);
        if ($nr === Flashcard\FlashcardBox::LAST_BOX) {
            $box = $box->withProperties([
                $this->lng->txt("glo_flashcards") => (string) $item_cnt
            ]);
        } else {
            $box = $box->withProperties([
                $this->lng->txt("glo_flashcards") => (string) $item_cnt,
                $this->lng->txt("glo_box_last_presented") => $last_access
            ]);
        }

        return $box;
    }

    public function confirmResetBoxes(): void
    {
        $yes_button = $this->ui_fac->button()->standard(
            $this->lng->txt("yes"),
            $this->ctrl->getLinkTarget($this, "resetBoxes")
        );
        $no_button = $this->ui_fac->button()->standard(
            $this->lng->txt("no"),
            $this->ctrl->getLinkTarget($this, "cancelResetBoxes")
        );
        $cbox = $this->ui_fac->messageBox()->confirmation($this->lng->txt("glo_boxes_really_reset"))
                             ->withButtons([$yes_button, $no_button]);
        $this->tpl->setContent($this->ui_ren->render($cbox));
    }

    public function cancelResetBoxes(): void
    {
        $this->ctrl->redirectByClass("ilglossarypresentationgui", "showFlashcards");
    }

    public function resetBoxes(): void
    {
        $this->manager->resetEntries();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("glo_boxes_reset"), true);
        $this->ctrl->redirectByClass("ilglossarypresentationgui", "showFlashcards");
    }
}
