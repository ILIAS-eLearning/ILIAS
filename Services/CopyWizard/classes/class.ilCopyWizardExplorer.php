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

include_once('./Services/UIComponent/Explorer/classes/class.ilExplorer.php');

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ingroup ServicesCopyWizard
*/
class ilCopyWizardExplorer extends ilExplorer
{
	private $lng;
	
	public function __construct($a_target)
	{
		global $lng,$objDefinition;
		
		$this->lng = $lng;
		$this->objDefinition = $objDefinition;
		parent::ilExplorer($a_target);
		$this->initItemCounter(1);
		
		$this->setTitleLength(ilObject::TITLE_LENGTH);
	}
	
   /**
	* get image path (may be overwritten by derived classes)
	*/
	public function getImage($a_name, $a_type = "", $a_obj_id = "")
	{
		return ilUtil::getImagePath('icon_'.$a_type.'.svg');
	}
	
	/**
	* check if links for certain object type are activated
	*
	* @param	string		$a_type			object type
	*
	* @return	boolean		true if linking is activated
	*/
	function isClickable($a_type, $a_ref_id = 0)
	{
		// always return false
		return false;
	}
	
	/**
	 * Force all nodes expanded
	 *
	 * @access public
	 * @param int node_id
	 * 
	 */
	public function forceExpanded()
	{
	 	return true;
	}
	
	
	/**
	 * Build option select
	 *
	 * @access public
	 * @param int node_ref_id
	 * 
	 */
	public function buildSelect($a_node_id,$a_type)
	{
		$selected = isset($_POST['cp_options'][$a_node_id]['type']) ?
			$_POST['cp_options'][$a_node_id]['type'] :
			ilCopyWizardOptions::COPY_WIZARD_COPY;
			
		if($this->objDefinition->allowCopy($a_type))
		{
			$options[ilCopyWizardOptions::COPY_WIZARD_COPY] = $this->lng->txt('copy');
		}
		if($this->objDefinition->allowLink($a_type))
		{
			$options[ilCopyWizardOptions::COPY_WIZARD_LINK] = $this->lng->txt('link');
		}
		$options[ilCopyWizardOptions::COPY_WIZARD_OMIT] = $this->lng->txt('omit');
		
	 	return ilUtil::formSelect($selected,'cp_options['.$a_node_id.'][type]',
			$options,
	 		false,true);
	 		
	}
	
}
?>