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

/**
 * Portfolio type gui implementations
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilExAssTypePortfolioGUI implements ilExAssignmentTypeGUIInterface
{
    use ilExAssignmentTypeGUIBase;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    /**
     * @inheritdoc
     */
    public function addEditFormCustomProperties(ilPropertyFormGUI $form): void
    {
        $lng = $this->lng;

        $rd_template = new ilRadioGroupInputGUI($lng->txt("exc_template"), "template");
        $rd_template->setRequired(true);
        $radio_no_template = new ilRadioOption($lng->txt("exc_without_template"), 0, $lng->txt("exc_without_template_info", "without_template_info"));
        $radio_with_template = new ilRadioOption($lng->txt("exc_with_template"), 1, $lng->txt("exc_with_template_info", "with_template_info"));

        $repo = new ilRepositorySelector2InputGUI($lng->txt("exc_portfolio_template"), "template_id");
        $repo->setRequired(true);
        $repo->getExplorerGUI()->setSelectableTypes(array("prtt"));
        $repo->getExplorerGUI()->setTypeWhiteList(array("root", "prtt", "cat", "crs", "grp", "fold"));
        $radio_with_template->addSubItem($repo);

        $rd_template->addOption($radio_no_template);
        $rd_template->addOption($radio_with_template);
        $form->addItem($rd_template);
    }

    /**
     * @inheritdoc
     */
    public function importFormToAssignment(ilExAssignment $ass, ilPropertyFormGUI $form): void
    {
        $ass->setPortfolioTemplateId(0);
        if ($form->getInput("template_id") && $form->getInput("template")) {
            $ass->setPortfolioTemplateId($form->getInput("template_id"));
        }
    }

    /**
     * @inheritdoc
     */
    public function getFormValuesArray(ilExAssignment $ass)
    {
        $values = [];

        if ($ass->getPortfolioTemplateId() > 0) {
            $values["template_id"] = $ass->getPortfolioTemplateId();
            $values["template"] = 1;
        }

        return $values;
    }

    public function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission): void
    {
    }

    public function buildSubmissionPropertiesAndActions(\ILIAS\Exercise\Assignment\PropertyAndActionBuilderUI $builder): void
    {
        global $DIC;

        $service = $DIC->exercise()->internal();
        $gui = $service->gui();
        $domain = $service->domain();
        $request = $gui->request();
        $back_ref_id = $request->getRefId();
        $lng = $domain->lng();
        $ilCtrl = $gui->ctrl();
        $f = $gui->ui()->factory();

        $submission = $this->getSubmission();

        $files_str = "";
        $buttons_str = "";
        $valid_prtf = false;
        $selected_prtf = $submission->getSelectedObject();
        if ($selected_prtf) {
            $portfolio_id = (int) $selected_prtf["filetitle"];

            // #11746
            if (\ilObject::_exists($portfolio_id, false, "prtf")) {
                $portfolio = new \ilObjPortfolio($portfolio_id, false);
                if ($portfolio->getTitle()) {
                    // #10116 / #12791
                    $ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", $portfolio_id);

                    $ref_id = $request->getRefId();
                    $ilCtrl->setParameterByClass("ilobjportfoliogui", "ref_id", $ref_id);
                    $ilCtrl->setParameterByClass("ilobjportfoliogui", "exc_back_ref_id", $back_ref_id);

                    $prtf_view = $ilCtrl->getLinkTargetByClass(array("ildashboardgui", "ilportfoliorepositorygui", "ilobjportfoliogui"), "preview");
                    $prtf_edit = $ilCtrl->getLinkTargetByClass(array("ildashboardgui", "ilportfoliorepositorygui", "ilobjportfoliogui"), "view");
                    $ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", "");
                    $ilCtrl->setParameterByClass("ilobjportfoliogui", "ref_id", "");

                    $builder->addProperty(
                        $builder::SEC_SUBMISSION,
                        $lng->txt("exc_portfolio_returned"),
                        $portfolio->getTitle()
                    );
                    if ($submission->canSubmit()) {
                        $button = $f->button()->primary(
                            $lng->txt("exc_edit_portfolio"),
                            $prtf_edit
                        );
                        $builder->setMainAction(
                            $builder::SEC_SUBMISSION,
                            $button
                        );
                    } else {
                        $link = $f->link()->standard(
                            $lng->txt("exc_view_portfolio"),
                            $prtf_view
                        );
                        $builder->addAction(
                            $builder::SEC_SUBMISSION,
                            $link
                        );
                    }
                    $valid_prtf = true;
                }
            }
            // remove invalid resource if no upload yet (see download below)
            elseif (substr($selected_prtf["filename"], -1) == "/") {
                // #16887
                $submission->deleteResourceObject();
            }
        }
        if ($submission->canSubmit()) {
            if (!$valid_prtf) {
                $button = $f->button()->primary(
                    $lng->txt("exc_create_portfolio"),
                    $ilCtrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "createPortfolioFromAssignment")
                );
                $builder->setMainAction(
                    $builder::SEC_SUBMISSION,
                    $button
                );
            }
            // #10462
            //selectPortfolio ( remove it? )
            $prtfs = count(ilObjPortfolio::getPortfoliosOfUser($submission->getUserId()));
            if ((!$valid_prtf && $prtfs)
                || ($valid_prtf && $prtfs > 1)) {
                $button = $f->button()->standard(
                    $lng->txt("exc_select_portfolio" . ($valid_prtf ? "_change" : "")),
                    $ilCtrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "selectPortfolio")
                );
                $builder->addAction(
                    $builder::SEC_SUBMISSION,
                    $button
                );
            }
            if ($valid_prtf) {
                $button = $f->button()->standard(
                    $lng->txt("exc_select_portfolio_unlink"),
                    $ilCtrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "askUnlinkPortfolio")
                );
                $builder->addAction(
                    $builder::SEC_SUBMISSION,
                    $button
                );
            }
        }

        if ($submission->hasSubmitted()) {
            $ilCtrl->setParameterByClass("ilExSubmissionFileGUI", "delivered", $selected_prtf["returned_id"]);
            $dl_link = $ilCtrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExSubmissionFileGUI"), "download");
            $ilCtrl->setParameterByClass("ilExSubmissionFileGUI", "delivered", "");

            $link = $f->link()->standard(
                $lng->txt("download"),
                $dl_link
            );
            $builder->addAction(
                $builder::SEC_SUBMISSION,
                $link
            );
        }
    }

}
