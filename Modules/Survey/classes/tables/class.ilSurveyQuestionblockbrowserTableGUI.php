<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesSurvey
*/

class ilSurveyQuestionblockbrowserTableGUI extends ilTable2GUI
{
    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    protected $editable = true;
    protected $writeAccess = false;
    protected $browsercolumns = array();
    
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_object, $a_write_access = false)
    {
        global $DIC;

        $this->rbacreview = $DIC->rbac()->review();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
    
        $this->setWriteAccess($a_write_access);

        $this->setFormName('surveyquestionblockbrowser');
        $this->setStyle('table', 'fullwidth');
        $this->addColumn('', 'f', '1%');
        $this->addColumn($this->lng->txt("title"), 'title', '');
        $this->addColumn($this->lng->txt("contains"), 'contains', '');
        $this->addColumn($this->lng->txt("obj_svy"), 'svy', '');

        $this->setPrefix('cb');
        $this->setSelectAllCheckbox('cb');
        
        $this->addMultiCommand('insertQuestionblocks', $this->lng->txt('insert'));

        $this->setRowTemplate("tpl.il_svy_svy_questionblockbrowser_row.html", "Modules/Survey");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        
        $this->enable('sort');
        $this->enable('header');
        $this->enable('select_all');
        $this->setFilterCommand('filterQuestionblockBrowser');
        $this->setResetCommand('resetfilterQuestionblockBrowser');
        
        $this->initFilter();
        $this->initData($a_object);
    }
    
    public function initData($a_object)
    {
        $arrFilter = array();
        foreach ($this->getFilterItems() as $item) {
            if ($item->getValue() !== false) {
                $arrFilter[$item->getPostVar()] = $item->getValue();
            }
        }
        $data = $a_object->getQuestionblocksTable($arrFilter);
        
        $this->setData($data);
    }

    /**
    * Init filter
    */
    public function initFilter()
    {
        $lng = $this->lng;
        $rbacreview = $this->rbacreview;
        $ilUser = $this->user;
        
        // title
        include_once("./Services/Form/classes/class.ilTextInputGUI.php");
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setValidationRegexp('/^[^%]+$/is');
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["title"] = $ti->getValue();
    }
    
    /**
     * fill row
     *
     * @access public
     * @param
     * @return
     */
    public function fillRow($data)
    {
        $ilUser = $this->user;
        $ilAccess = $this->access;

        $this->tpl->setVariable('QUESTIONBLOCK_ID', $data["questionblock_id"]);
        $this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($data["title"]));
        $this->tpl->setVariable("CONTAINS", ilUtil::prepareFormOutput($data["contains"]));
        $this->tpl->setVariable("SVY", ilUtil::prepareFormOutput($data['svy']));
    }
    
    public function setEditable($value)
    {
        $this->editable = $value;
    }
    
    public function getEditable()
    {
        return $this->editable;
    }

    public function setWriteAccess($value)
    {
        $this->writeAccess = $value;
    }
    
    public function getWriteAccess()
    {
        return $this->writeAccess;
    }
}
