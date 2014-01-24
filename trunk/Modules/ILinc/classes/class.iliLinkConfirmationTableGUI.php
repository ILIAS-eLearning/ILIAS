<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * iliLinkConfirmationTableGUI
 *
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @extends ilTable2GUI
 */
class iliLinkConfirmationTableGUI extends ilTable2GUI
{	
	public function __construct($a_parent_obj, $a_data, $a_cmd, $a_default_form_action)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_cmd);
		
		$this->setData($a_data);
		
		$this->addColumn($lng->txt('type'), 'type', '1%');
		$this->addColumn($lng->txt('title'), 'title', '33%');
		$this->addColumn($lng->txt('description'), 'description', '33%');
		$this->addColumn($lng->txt('last_change'), 'last_change', '33%');		
		
		$this->enable('sort');
		$this->enable('header');
		$this->setLimit(32000);
		$this->setPrefix('confirmation');		
		$this->setFormName('confirmation');
			
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_default_form_action));
		
		$this->setRowTemplate('tpl.icrs_confirmation_row.html', 'Modules/ILinc');		
	}
}
?>