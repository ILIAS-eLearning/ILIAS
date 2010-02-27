<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Export User Interface Class
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
* @ingroup	ServicesExport
*
* @ilCtrl_Calls ilExportGUI:
*/
class ilExportGUI
{
	protected $formats = array();
	
	/**
	 * Constuctor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_parent_gui)
	{
		global $lng;
		
		$this->parent_gui = $a_parent_gui;
		$this->obj = $a_parent_gui->object;
		$lng->loadLanguageModule("exp");
	}
	
	/**
	 * Add formats
	 *
	 * @param	array	formats
	 */
	function addFormat($a_key, $a_create_txt)
	{
		$this->formats[] = array("key" => $a_key, "txt" => $a_create_txt);
	}
	
	/**
	 * Get formats
	 *
	 * @return	array	formats
	 */
	function getFormats()
	{
		return $this->formats;
	}
	
	/**
	 * Execute command
	 *
	 * @param
	 * @return
	 */
	function executeCommand()
	{
		global $ilCtrl;
	
		$cmd = $ilCtrl->getCmd("listExportFiles");
		
		switch ($cmd)
		{
			case "listExportFiles":
				$this->$cmd();
				break;
				
			default:
				if (substr($cmd, 0, 7) == "create_")
				{
					$this->createExportFile();
				}
				break;
		}
	}
	
	/**
	 * List export files
	 *
	 * @param
	 * @return
	 */
	function listExportFiles()
	{
		global $tpl, $ilToolbar, $ilCtrl;

		// creation buttons
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
		if (count($this->getFormats()) > 1)
		{
			
		}
		else
		{
			$format = $this->getFormats();
			$format = $format[0];
			$ilToolbar->addFormButton($format["txt"], "create_".$format["key"]);
		}
	
		include_once("./Services/Export/classes/class.ilExportTableGUI.php");
		$table = new ilExportTableGUI($this, "listExportFiles", $this->obj);
		
		$tpl->setContent($table->getHTML());
		
	}
	
	/**
	 * Create export file
	 *
	 * @param
	 * @return
	 */
	function createExportFile()
	{
		global $ilCtrl;
		
		$format = substr($ilCtrl->getCmd(), 7);
		foreach ($this->getFormats() as $f)
		{
			if ($f["key"] == $format)
			{
				if ($format == "xml")
				{
					include_once("./Services/Export/classes/class.ilExport.php");
					ilExport::_exportObject($this->obj->getType(),
						$this->obj->getId(), "4.1.0");
				}
			}
		}
		$ilCtrl->redirect($this, "listExportFiles");
	}
}
