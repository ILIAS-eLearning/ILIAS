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
 * Blog type gui implementations
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilExAssTypeBlogGUI implements ilExAssignmentTypeGUIInterface
{
    use ilExAssignmentTypeGUIBase;

    /**
     * @inheritdoc
     */
    public function addEditFormCustomProperties(ilPropertyFormGUI $form): void
    {
    }

    /**
     * @inheritdoc
     */
    public function importFormToAssignment(ilExAssignment $ass, ilPropertyFormGUI $form): void
    {
    }

    /**
     * @inheritdoc
     */
    public function getFormValuesArray(ilExAssignment $ass): array
    {
        return [];
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
        $f = $gui->ui()->factory();
        $lng = $domain->lng();
        $ilCtrl = $gui->ctrl();
        $submission = $this->getSubmission();


        $wsp_tree = new \ilWorkspaceTree($submission->getUserId());

        // #12939
        if (!$wsp_tree->getRootId()) {
            $wsp_tree->createTreeForUser($submission->getUserId());
        }

        $files_str = "";
        $buttons_str = "";
        $valid_blog = false;
        $selected_blog = $submission->getSelectedObject();
        if ($selected_blog) {
            $blog_id = (int) $selected_blog["filetitle"];
            $node = $wsp_tree->getNodeData($blog_id);
            if ($node["title"]) {
                // #10116
                $ilCtrl->setParameterByClass("ilobjbloggui", "wsp_id", $blog_id);
                $blog_link = $ilCtrl->getLinkTargetByClass(array("ildashboardgui", "ilpersonalworkspacegui", "ilobjbloggui"), "");
                $ilCtrl->setParameterByClass("ilobjbloggui", "wsp_id", "");
                $valid_blog = true;
                $builder->addProperty(
                    $builder::SEC_SUBMISSION,
                    $lng->txt("exc_blog_returned"),
                    $node["title"]
                );
                $button = $f->button()->standard(
                    $lng->txt("exc_edit_blog"),
                    $blog_link
                );
                $builder->addAction(
                    $builder::SEC_SUBMISSION,
                    $button
                );
            }
            // remove invalid resource if no upload yet (see download below)
            elseif (substr($selected_blog["filename"], -1) == "/") {
                // #16887
                $submission->deleteResourceObject();
            }
        }
        if ($submission->canSubmit()) {
            if (!$valid_blog) {
                $button = $f->button()->primary(
                    $lng->txt("exc_create_blog"),
                    $ilCtrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "createBlog")
                );
                $builder->setMainAction(
                    $builder::SEC_SUBMISSION,
                    $button
                );
            }
            // #10462
            $blogs = count($wsp_tree->getObjectsFromType("blog"));
            if ((!$valid_blog && $blogs)
                || ($valid_blog && $blogs > 1)) {
                $button = $f->button()->standard(
                    $lng->txt("exc_select_blog" . ($valid_blog ? "_change" : "")),
                    $ilCtrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExSubmissionObjectGUI"), "selectBlog")
                );
                $builder->addAction(
                    $builder::SEC_SUBMISSION,
                    $button
                );
            }
        }

        if ($submission->hasSubmitted()) {
            $ilCtrl->setParameterByClass("ilExSubmissionFileGUI", "delivered", $selected_blog["returned_id"]);
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
