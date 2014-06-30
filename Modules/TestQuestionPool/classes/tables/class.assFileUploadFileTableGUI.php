<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @author Björn Heyser <bheyser@databay.de>
* @version $Id$
*
* @ingroup ModulesGroup
*/

class assFileUploadFileTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $formname = 'test_output')
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
	 	
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setFormName($formname);
		$this->setStyle('table', 'std');
		$this->addColumn('','f','1%');
		$this->addColumn($this->lng->txt('filename'),'filename', '70%');
		$this->addColumn($this->lng->txt('date'),'date', '29%');
		$this->setDisplayAsBlock(true);
	 	
		$this->setPrefix('deletefiles');
		$this->setSelectAllCheckbox('deletefiles');
		
		$this->addCommandButton($a_parent_cmd, $this->lng->txt('delete'));
		$this->setRowTemplate("tpl.il_as_qpl_fileupload_file_row.html", "Modules/TestQuestionPool");
		
		$this->disable('sort');
		$this->disable('linkbar');
		$this->enable('header');
		$this->enable('select_all');
	}
	
	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($a_set)
	{
		global $ilUser,$ilAccess;
		
		$this->tpl->setVariable('VAL_ID', $a_set['solution_id']);
		if (strlen($a_set['webpath']))
		{
			$this->tpl->setVariable('VAL_FILE', '<a href="' . $a_set['webpath'] . $a_set['value1'] . '" target=\"_blank\">' . ilUtil::prepareFormOutput($a_set['value2']) . '</a>');
		}
		else
		{
			$this->tpl->setVariable('VAL_FILE', ilUtil::prepareFormOutput($a_set['value2']));
		}
		ilDatePresentation::setUseRelativeDates(false);
		$this->tpl->setVariable('VAL_DATE', ilDatePresentation::formatDate(new ilDateTime($a_set["tstamp"],IL_CAL_UNIX)));
	}
	
}
?>