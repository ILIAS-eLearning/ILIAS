<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesMail
*/
include_once 'Services/Form/classes/class.ilFormPropertyGUI.php';

class ilMailFormAttachmentPropertyGUI extends ilFormPropertyGUI
{
	public $buttonLabel;
	public $items = array();
	
	/**
	 * Form Element for showing Mail Attachments
	 * @param	string	Buttonlabel (e.g. edit or add) 
	 */
	public function __construct($buttonLabel)
	{
		global $lng;
		
		$this->buttonLabel = $buttonLabel;
		parent::__construct($lng->txt('attachments'));
	}
	
	/**
	 * Add Attachment Item to list
	 * @param	string	Label for item including additional information
	 *			like Filesize.
	 */
	public function addItem($label)
	{
		$this->items[] = $label;
	}
	
	public function insert($a_tpl)
	{
		$tpl = new ilTemplate('tpl.mail_new_attachments.html', true, true, 'Services/Mail');

		foreach($this->items as $item)
		{
			$tpl->setCurrentBlock('attachment_list_item');
			$tpl->setVariable('ATTACHMENT_LABEL', $item);
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable('ATTACHMENT_BUTTON_LABEL', $this->buttonLabel);
		
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();	
	}
}

?>