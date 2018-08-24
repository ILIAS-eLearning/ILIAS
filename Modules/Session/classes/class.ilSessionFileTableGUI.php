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

/** 
*
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ModulesCourse 
*/

include_once('Services/Table/classes/class.ilTable2GUI.php');
class ilSessionFileTableGUI extends ilTable2GUI
{
	protected $lng = null;
	protected $ctrl;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_parent_obj,$a_parent_cmd = '')
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
	 	
	 	parent::__construct($a_parent_obj,$a_parent_cmd);
	 	$this->addColumn('','f',1);
	 	$this->addColumn($this->lng->txt('filename'),'filename',"60%");
	 	$this->addColumn($this->lng->txt('filesize'),'filesize',"20%");
	 	$this->addColumn($this->lng->txt('filetype'),'filetype',"20%");
	 	
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.sess_file_row.html","Modules/Session");
		$this->setDefaultOrderField("filname");
		$this->setDefaultOrderDirection("desc");
	}
	
	/**
	 * Fill row
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function fillRow($a_set)
	{
	 	$this->tpl->setVariable('VAL_ID',$a_set['id']);
	 	$this->tpl->setVariable('VAL_FILENAME',$a_set['filename']);
	 	$this->tpl->setVariable('VAL_FILETYPE',$a_set['filetype']);
	 	$this->tpl->setVariable('VAL_FILESIZE',$a_set['filesize']);
	}
}


?>