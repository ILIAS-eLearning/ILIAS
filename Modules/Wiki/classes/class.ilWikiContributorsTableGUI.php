<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for listing users that contributed to the wiki
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesWiki
*/
class ilWikiContributorsTableGUI extends ilTable2GUI
{
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd = "",
        $a_wiki_id
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
    }
    
    /**
    * Get contributors of wiki
    */
    public function getContributors()
    {
        include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
        $contributors = ilWikiPage::getWikiContributors($this->wiki_id);
        $this->setDefaultOrderField("lastname");
        $this->setDefaultOrderDirection("asc");
        $this->setData($contributors);
    }
    
    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        include_once("./Services/Tracking/classes/class.ilLPMarks.php");
        include_once("./Modules/Wiki/classes/class.ilWikiContributor.php");

        if (ilObject::_exists($a_set["user_id"])) {
            arsort($a_set["pages"]);

            // pages
            foreach ($a_set["pages"] as $page_id => $cnt) {
                if ($page_id > 0) {
                    include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
                    $title = ilWikiPage::lookupTitle($page_id);
                    $this->tpl->setCurrentBlock("page");
                    $this->tpl->setVariable("PAGE", $title);
                    $this->tpl->setVariable("CNT", $cnt);
                    $this->tpl->parseCurrentBlock();
                }
            }
            
            $this->tpl->setVariable(
                "TXT_LINKED_USER",
                $user["lastname"] . ", " . $user["firstname"] . " [" . $login . "]"
            );
                
            // profile link
            //$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "user", $a_set["user"]);
            //$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "back_url",
            //	rawurlencode($ilCtrl->getLinkTarget($this->getParentObject(), $this->getParentCmd())));
            //$this->tpl->setVariable("USER_LINK",
            //	$ilCtrl->getLinkTargetByClass("ilpublicuserprofilegui", "getHTML"));
            $img = ilObjUser::_getPersonalPicturePath($a_set["user_id"], "xsmall");
            $this->tpl->setVariable("IMG_USER", $img);
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
                $this->parent_obj->object->getId()
            );
            $this->tpl->setVariable(
                "VAL_LCOMMENT",
                ilUtil::prepareFormOutput($lpcomment)
            );

            // status
            //$status = ilExerciseMembers::_lookupStatus($this->object->getId(), $member_id);
            $status = ilWikiContributor::_lookupStatus($this->parent_obj->object->getId(), $a_set["user_id"]);
            $this->tpl->setVariable("SEL_" . $status, ' selected="selected" ');
            $this->tpl->setVariable("TXT_NOTGRADED", $lng->txt("wiki_notgraded"));
            $this->tpl->setVariable("TXT_PASSED", $lng->txt("wiki_passed"));
            $this->tpl->setVariable("TXT_FAILED", $lng->txt("wiki_failed"));
            $this->tpl->setVariable("VAL_NOTGRADED", ilWikiContributor::STATUS_NOT_GRADED);
            $this->tpl->setVariable("VAL_PASSED", ilWikiContributor::STATUS_PASSED);
            $this->tpl->setVariable("VAL_FAILED", ilWikiContributor::STATUS_FAILED);
            if (($sd = ilWikiContributor::_lookupStatusTime($this->parent_obj->object->getId(), $a_set["user_id"])) > 0) {
                $this->tpl->setCurrentBlock("status_date");
                $this->tpl->setVariable("TXT_LAST_CHANGE", $lng->txt("last_change"));
                $this->tpl->setVariable(
                    "VAL_STATUS_DATE",
                    ilDatePresentation::formatDate(new ilDateTime($sd, IL_CAL_DATETIME))
                );
                $this->tpl->parseCurrentBlock();
            }
            switch ($status) {
                case ilWikiContributor::STATUS_PASSED: 	$pic = "scorm/passed.svg"; break;
                case ilWikiContributor::STATUS_FAILED:	$pic = "scorm/failed.svg"; break;
                default: 		$pic = "scorm/not_attempted.svg"; break;
            }
            $this->tpl->setVariable("IMG_STATUS", ilUtil::getImagePath($pic));
            $this->tpl->setVariable("ALT_STATUS", $lng->txt("wiki_" . $status));
            
            // mark
            $this->tpl->setVariable("TXT_MARK", $lng->txt("wiki_mark"));
            $this->tpl->setVariable(
                "NAME_MARK",
                "mark[" . $a_set["user_id"] . "]"
            );
            $mark = ilLPMarks::_lookupMark($a_set["user_id"], $this->parent_obj->object->getId());

            $this->tpl->setVariable(
                "VAL_MARK",
                ilUtil::prepareFormOutput($mark)
            );
        }
    }
}
