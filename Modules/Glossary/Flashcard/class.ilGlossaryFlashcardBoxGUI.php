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
 * GUI class for glossary flashcard boxes
 * @author Thomas Famula <famula@leifos.de>
 */
class ilGlossaryFlashcardBoxGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected Presentation\PresentationGUIRequest $request;
    protected Flashcard\FlashcardManager $manager;
    protected int $box_nr = 0;
    protected array $initial_terms_in_box = [];
    protected array $terms_in_box = [];
    protected int $current_term_id = 0;
    protected ilObjGlossary $glossary;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->request = $DIC->glossary()
                             ->internal()
                             ->gui()
                             ->presentation()
                             ->request();
        $gs = $DIC->glossary()->internal();
        $this->manager = $gs->domain()->flashcard($this->request->getRefId());

        $this->ctrl->saveParameter($this, ["box_id"]);
        $this->box_nr = $this->request->getBoxId();
        $this->initial_terms_in_box = $this->manager->getSessionInitialTerms($this->box_nr);
        $this->terms_in_box = $this->manager->getSessionTerms($this->box_nr);
        $this->current_term_id = $this->terms_in_box[0] ?? 0;
        $this->glossary = new ilObjGlossary($this->request->getRefId());
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd("show");
                $ret = $this->$cmd();
                break;
        }
    }

    public function show(): void
    {
        if ($this->box_nr === Flashcard\FlashcardBox::FIRST_BOX) {
            $cnt_all = count($this->manager->getUserTermIdsForBox($this->box_nr))
                + count($this->manager->getAllTermsWithoutEntry());
            $cnt_remaining = count($this->manager->getNonTodayUserTermIdsForBox($this->box_nr))
                + count($this->manager->getAllTermsWithoutEntry());
        } else {
            $cnt_all = count($this->manager->getUserTermIdsForBox($this->box_nr));
            $cnt_remaining = count($this->manager->getNonTodayUserTermIdsForBox($this->box_nr));
        }
        $cnt_today = count($this->manager->getTodayUserTermIdsForBox($this->box_nr));

        if (($this->box_nr === Flashcard\FlashcardBox::FIRST_BOX
                && !$this->manager->getAllTermsWithoutEntry()
                && !$this->manager->getNonTodayUserTermIdsForBox($this->box_nr)
                && $this->manager->getTodayUserTermIdsForBox($this->box_nr))
            || ($this->box_nr !== Flashcard\FlashcardBox::FIRST_BOX
                && !$this->manager->getNonTodayUserTermIdsForBox($this->box_nr)
                && $this->manager->getTodayUserTermIdsForBox($this->box_nr))) {
            $all_button = $this->ui_fac->button()->standard(
                sprintf($this->lng->txt("glo_use_all_flashcards"), $cnt_all),
                $this->ctrl->getLinkTarget($this, "showAllItems")
            );
            $cbox = $this->ui_fac->messageBox()->confirmation(
                sprintf($this->lng->txt("glo_flashcards_from_today_only_info"), $cnt_all)
            )->withButtons([$all_button]);
            $this->tpl->setContent($this->ui_ren->render($cbox));
        } elseif ($this->manager->getTodayUserTermIdsForBox($this->box_nr)) {
            $remaining_button = $this->ui_fac->button()->standard(
                sprintf($this->lng->txt("glo_use_remaining_flashcards"), $cnt_remaining),
                $this->ctrl->getLinkTarget($this, "showRemainingItems")
            );
            $all_button = $this->ui_fac->button()->standard(
                sprintf($this->lng->txt("glo_use_all_flashcards"), $cnt_all),
                $this->ctrl->getLinkTarget($this, "showAllItems")
            );
            $cbox = $this->ui_fac->messageBox()->confirmation(
                sprintf($this->lng->txt("glo_flashcards_from_today_confirmation"), $cnt_today, $cnt_remaining, $cnt_all)
            )->withButtons([$remaining_button, $all_button]);
            $this->tpl->setContent($this->ui_ren->render($cbox));
        } else {
            $this->showAllItems();
        }
    }

    public function showAllItems(): void
    {
        $this->showItems(true);
    }

    public function showRemainingItems(): void
    {
        $this->showItems(false);
    }

    public function showItems(bool $all): void
    {
        if ($all) {
            $terms = $this->manager->getUserTermIdsForBox($this->box_nr);
        } else {
            $terms = $this->manager->getNonTodayUserTermIdsForBox($this->box_nr);
        }
        if ($this->box_nr === Flashcard\FlashcardBox::FIRST_BOX) {
            $terms_without_entry = $this->manager->getAllTermsWithoutEntry();
            $terms = array_merge($terms_without_entry, $terms);
        }
        $this->manager->setSessionInitialTerms($this->box_nr, $terms);
        $this->manager->setSessionTerms($this->box_nr, $terms);
        $this->manager->createOrUpdateBoxAccessEntry($this->box_nr);
        $this->ctrl->redirect($this, "showHidden");
    }

    public function showHidden(): void
    {
        $this->tpl->setDescription($this->lng->txt("glo_box") . " " . $this->box_nr);

        $progress = $this->manager->getBoxProgress($this->terms_in_box, $this->initial_terms_in_box);
        $progress_bar = ilProgressBar::getInstance();
        $progress_bar->setCurrent($progress);

        if ($this->glossary->getFlashcardsMode() === "term") {
            $flashcard = $this->ui_fac->panel()->standard(
                $this->lng->txt("term") . ": " . $this->getTermText(),
                $this->ui_fac->legacy("???")
            );
        } else {
            $flashcard = $this->ui_fac->panel()->standard(
                $this->lng->txt("term") . ": ???",
                $this->ui_fac->legacy($this->getDefinitionPage())
            );
        }

        if ($this->glossary->getFlashcardsMode() === "term") {
            $btn_show = $this->ui_fac->button()->standard(
                $this->lng->txt("glo_show_definition"),
                $this->ctrl->getLinkTarget($this, "showRevealed")
            );
        } else {
            $btn_show = $this->ui_fac->button()->standard(
                $this->lng->txt("glo_show_term"),
                $this->ctrl->getLinkTarget($this, "showRevealed")
            );
        }

        $btn_quit = $this->ui_fac->button()->standard(
            $this->lng->txt("glo_quit_box"),
            $this->ctrl->getLinkTargetByClass("ilglossarypresentationgui", "showFlashcards")
        );

        $html = $progress_bar->render()
            . $this->ui_ren->render($flashcard)
            . $this->ui_ren->render($btn_show)
            . $this->ui_ren->render($btn_quit);
        $this->tpl->setContent($html);
    }

    public function showRevealed(): void
    {
        $this->tpl->setDescription($this->lng->txt("glo_box") . " " . $this->box_nr);


        $progress = $this->manager->getBoxProgress($this->terms_in_box, $this->initial_terms_in_box);
        $progress_bar = ilProgressBar::getInstance();
        $progress_bar->setCurrent($progress);

        $flashcard = $this->ui_fac->panel()->standard(
            $this->lng->txt("term") . ": " . $this->getTermText(),
            $this->ui_fac->legacy($this->getDefinitionPage())
        );

        $btn_correct = $this->ui_fac->button()->standard(
            $this->lng->txt("glo_answered_correctly"),
            $this->ctrl->getLinkTarget($this, "answerCorrectly")
        );

        $btn_not_correct = $this->ui_fac->button()->standard(
            $this->lng->txt("glo_answered_not_correctly"),
            $this->ctrl->getLinkTarget($this, "answerInCorrectly")
        );

        $html = $progress_bar->render()
            . $this->ui_ren->render($flashcard)
            . $this->ui_ren->render($btn_correct)
            . $this->ui_ren->render($btn_not_correct);
        $this->tpl->setContent($html);
    }

    public function answerCorrectly(): void
    {
        $this->answer(true);
    }

    public function answerInCorrectly(): void
    {
        $this->answer(false);
    }

    public function answer(bool $correct): void
    {
        $this->manager->createOrUpdateUserTermEntry($this->current_term_id, $correct);
        array_shift($this->terms_in_box);
        $this->manager->setSessionTerms($this->box_nr, $this->terms_in_box);
        if ($this->terms_in_box) {
            $this->ctrl->redirect($this, "showHidden");
        }
        $this->tpl->setOnScreenMessage("success", $this->lng->txt("glo_box_completed"), true);
        $this->ctrl->redirectByClass("ilglossarypresentationgui", "showFlashcards");
    }

    protected function getTermText(): string
    {
        $text = ilGlossaryTerm::_lookGlossaryTerm($this->current_term_id);
        return $text;
    }

    protected function getDefinitionPage(): string
    {
        $page_gui = new ilGlossaryDefPageGUI($this->current_term_id);
        return $page_gui->showPage();
    }
}
