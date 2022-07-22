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
 * TableGUI class for listing users that contributed to the wiki
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiContributorsTableGUI extends ilTable2GUI
{
    protected int $wiki_id;
    protected \ILIAS\DI\UIServices $ui;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_wiki_id
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->wiki_id = $a_wiki_id;
        
        $this->addColumn("", "", "1");
        //$this->addColumn("", "", "1");
        $this->addColumn($lng->txt("wiki_contributor"), "", "33%");
        $this->addColumn($lng->txt("wiki_page_changes"), "", "33%");
        $this->addColumn($lng->txt("wiki_grading"), "", "33%");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.table_row_contributors.html",
            "Modules/Wiki"
        );
        $this->getContributors();
        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), "saveGrading"));
        $this->addCommandButton("saveGrading", $lng->txt("save"));
        //$this->addMultiCommand("saveGrading", $lng->txt("save"));
        
        $this->setShowRowsSelector(true);
        
        $this->setTitle($lng->txt("wiki_contributors"));
        $this->ui = $DIC->ui();
    }
    
    public function getContributors() : void
    {
        $contributors = ilWikiPage::getWikiContributors($this->wiki_id);
        $this->setDefaultOrderField("lastname");
        $this->setDefaultOrderDirection("asc");
        $this->setData($contributors);
    }
    
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        
        if (ilObject::_exists($a_set["user_id"])) {
            arsort($a_set["pages"]);

            // pages
            foreach ($a_set["pages"] as $page_id => $cnt) {
                if ($page_id > 0) {
                    $title = ilWikiPage::lookupTitle($page_id);
                    $this->tpl->setCurrentBlock("page");
                    $this->tpl->setVariable("PAGE", $title);
                    $this->tpl->setVariable("CNT", $cnt);
                    $this->tpl->parseCurrentBlock();
                }
            }

            /*
            $this->tpl->setVariable(
                "TXT_LINKED_USER",
                $user["lastname"] . ", " . $user["firstname"] . " [" . $login . "]"
            );*/
                
            // profile link
            //$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "user", $a_set["user"]);
            //$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "back_url",
            //	rawurlencode($ilCtrl->getLinkTarget($this->getParentObject(), $this->getParentCmd())));
            //$this->tpl->setVariable("USER_LINK",
            //	$ilCtrl->getLinkTargetByClass("ilpublicuserprofilegui", "getHTML"));
            $avatar = ilObjUser::_getAvatar((int) $a_set["user_id"]);
            $this->tpl->setVariable("AVATAR", $this->ui->renderer()->render($avatar));
            $this->tpl->setVariable(
                "TXT_NAME",
                htmlspecialchars($a_set["lastname"] . ", " . $a_set["firstname"])
            );
            $this->tpl->setVariable("USER_ID", $a_set["user_id"]);
                
            // comment for learner
            $this->tpl->setVariable("TXT_LCOMMENT", $lng->txt("wiki_comment_for_learner"));
            $this->tpl->setVariable(
                "NAME_LCOMMENT",
                "lcomment[" . $a_set["user_id"] . "]"
            );
            $lpcomment = ilLPMarks::_lookupComment(
                $a_set["user_id"],
                $this->parent_obj->getObject()->getId()
            );
            $this->tpl->setVariable(
                "VAL_LCOMMENT",
                ilLegacyFormElementsUtil::prepareFormOutput($lpcomment)
            );

            // status
            //$status = ilExerciseMembers::_lookupStatus($this->object->getId(), $member_id);
            $status = ilWikiContributor::_lookupStatus($this->parent_obj->getObject()->getId(), $a_set["user_id"]);
            $this->tpl->setVariable("SEL_" . $status, ' selected="selected" ');
            $this->tpl->setVariable("TXT_NOTGRADED", $lng->txt("wiki_notgraded"));
            $this->tpl->setVariable("TXT_PASSED", $lng->txt("wiki_passed"));
            $this->tpl->setVariable("TXT_FAILED", $lng->txt("wiki_failed"));
            $this->tpl->setVariable("VAL_NOTGRADED", ilWikiContributor::STATUS_NOT_GRADED);
            $this->tpl->setVariable("VAL_PASSED", ilWikiContributor::STATUS_PASSED);
            $this->tpl->setVariable("VAL_FAILED", ilWikiContributor::STATUS_FAILED);
            if (($sd = ilWikiContributor::_lookupStatusTime($this->parent_obj->getObject()->getId(), $a_set["user_id"])) > 0) {
                $this->tpl->setCurrentBlock("status_date");
                $this->tpl->setVariable("TXT_LAST_CHANGE", $lng->txt("last_change"));
                $this->tpl->setVariable(
                    "VAL_STATUS_DATE",
                    ilDatePresentation::formatDate(new ilDateTime($sd, IL_CAL_DATETIME))
                );
                $this->tpl->parseCurrentBlock();
            }

            $icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_LONG);

            switch ($status) {
                case ilWikiContributor::STATUS_PASSED:
                    $icon_rendered = $icons->renderIcon(
                        $icons->getImagePathCompleted(),
                        $lng->txt("wiki_passed")
                    );
                    break;
                case ilWikiContributor::STATUS_FAILED:
                    $icon_rendered = $icons->renderIcon(
                        $icons->getImagePathFailed(),
                        $lng->txt("wiki_failed")
                    );
                    break;
                default:
                    $icon_rendered = $icons->renderIcon(
                        $icons->getImagePathNotAttempted(),
                        $lng->txt("wiki_notgraded")
                    );
                    break;
            }

            $this->tpl->setVariable("ICON_STATUS", $icon_rendered);
            
            // mark
            $this->tpl->setVariable("TXT_MARK", $lng->txt("wiki_mark"));
            $this->tpl->setVariable(
                "NAME_MARK",
                "mark[" . $a_set["user_id"] . "]"
            );
            $mark = ilLPMarks::_lookupMark($a_set["user_id"], $this->parent_obj->getObject()->getId());

            $this->tpl->setVariable(
                "VAL_MARK",
                ilLegacyFormElementsUtil::prepareFormOutput($mark)
            );
        }
    }
}
