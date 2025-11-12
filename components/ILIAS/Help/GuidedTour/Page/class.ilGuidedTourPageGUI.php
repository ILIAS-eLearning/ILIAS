<?php

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

declare(strict_types=1);

/**
 * @ilCtrl_Calls ilGuidedTourPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilGuidedTourPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilGuidedTourPageGUI: ilPropertyFormGUI, ilInternalLinkGUI, ilPageMultiLangGUI
 */
class ilGuidedTourPageGUI extends ilPageObjectGUI
{
    protected \ILIAS\Help\GuidedTour\InternalDomainService $gt_domain;

    public function __construct(
        int $a_id = 0,
        int $a_old_nr = 0,
        string $a_lang = ""
    ) {
        global $DIC;

        parent::__construct("gdtr", $a_id, $a_old_nr, false, $a_lang);
        $this->gt_domain = $DIC->help()->internal()->domain()->guidedTour();
    }

    public function getProfileBackUrl(): string
    {
        return "#";
    }

    public function finishEditing(): void
    {
        $this->ctrl->returnToParent($this);
    }

    public function showPageFullscreen(): void
    {
        $tpl = new ilGlobalTemplate("tpl.fullscreen.html", true, true, "components/ILIAS/COPage");
        $this->setTemplate($tpl);
        $this->addResourcesToTemplate($tpl);
        $tpl->addCss(ilUtil::getStyleSheetLocation());
        $tpl->addCss(ilObjStyleSheet::getContentStylePath($this->getStyleId()));
        $this->setTemplateOutput(false);
        $this->setHeader("");
        $ret = $this->showPage();
        $tpl->setVariable("MEDIA_CONTENT", "<div>" . $ret .
            $this->renderButtons() . "</div>");
        $tpl->printToStdout();
        exit;
    }

    protected function renderButtons(): string
    {
        $lng = $this->gt_domain->lng();
        $lng->loadLanguageModule("help");
        return "<p>" .
            $this->gui->button(
                $lng->txt("gdtr_next_step"),
                "#"
            )->render(["gdtr-type" => "next"]) .
            $this->gui->button(
                $lng->txt("gdtr_close"),
                "#"
            )->render(["gdtr-type" => "close"]) .
            "</p>";
    }

}
