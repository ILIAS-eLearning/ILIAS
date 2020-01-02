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
* @version $Id: class.ilSurveySavePhraseTableGUI.php 24564 2010-07-12 08:28:45Z hschottm $
*
* @ingroup ModulesSurveyQuestionPool
*/

class ilSurveySavePhraseTableGUI extends ilTable2GUI
{
    protected $confirmdelete;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->confirmdelete = $confirmdelete;
    
        $this->setFormName('phrases');
        $this->setStyle('table', 'fullwidth');

        $this->addColumn($this->lng->txt("answer"), '', '');
        $this->addColumn($this->lng->txt("use_other_answer"), '', '');
        $this->addColumn($this->lng->txt("scale"), '', '');

        $this->setRowTemplate("tpl.il_svy_qpl_phrase_save_row.html", "Modules/SurveyQuestionPool");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->disable('sort');
        $this->disable('select_all');
        $this->enable('header');
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
        $this->tpl->setVariable("ANSWER", $data["answer"]);
        $this->tpl->setVariable("OPEN_ANSWER", ($data["other"]) ? $this->lng->txt('yes') : $this->lng->txt('no'));
        $this->tpl->setVariable("SCALE", $data["scale"]);
    }
}
