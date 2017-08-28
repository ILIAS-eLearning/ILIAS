<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Object/classes/class.ilSubItemListGUI.php';

/** 
* Show forum threads
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ModulesForum
*/
class ilObjForumSubItemListGUI extends ilSubItemListGUI
{
	/**
	 * get html 
	 * @return
	 */
	public function getHTML()
	{
		global $lng;
		
		foreach($this->getSubItemIds(true) as $sub_item)
		{
			if(is_object($this->getHighlighter()) and strlen($this->getHighlighter()->getContent($this->getObjId(),$sub_item)))
			{
				$this->tpl->setCurrentBlock('sea_fragment');
				$this->tpl->setVariable('TXT_FRAGMENT',$this->getHighlighter()->getContent($this->getObjId(),$sub_item));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock('subitem');
			$this->tpl->setVariable('SUBITEM_TYPE',$lng->txt('thread'));
			$this->tpl->setVariable('SEPERATOR',':');
			
			$this->getItemListGUI()->setChildId($sub_item);
			$this->tpl->setVariable('LINK',$this->getItemListGUI()->getCommandLink('thread'));
			$this->tpl->setVariable('TARGET',$this->getItemListGUI()->getCommandFrame(''));
			include_once './Modules/Forum/classes/class.ilObjForum.php';
			$this->tpl->setVariable('TITLE',ilObjForum::_lookupThreadSubject($sub_item));
			
			// begin-patch mime_filter
			if(count($this->getSubItemIds(true)) > 1)
			{
				$this->parseRelevance($sub_item);
			}
			// end-patch mime_filter
			
			$this->tpl->parseCurrentBlock();
		}
		
		$this->showDetailsLink();
		
		return $this->tpl->get();	 
	}
}
?>
