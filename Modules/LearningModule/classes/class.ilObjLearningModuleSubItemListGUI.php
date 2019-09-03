<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup 
*/
class ilObjLearningModuleSubItemListGUI extends ilSubItemListGUI
{
	/**
	 * @var ilObjUser
	 */
	protected $user;


	/**
	 * Constructor
	 */
	function __construct($a_cmd_class)
	{
		global $DIC;
		parent::__construct($a_cmd_class);

		$this->user = $DIC->user();
	}

	
	/**
	 * get html 
	 * @return
	 */
	public function getHTML()
	{
		$lng = $this->lng;
		$ilUser = $this->user;
		
		foreach($this->getSubItemIds(true) as $sub_item)
		{
			if(is_object($this->getHighlighter()) and strlen($this->getHighlighter()->getContent($this->getObjId(),$sub_item)))
			{
				$this->tpl->setCurrentBlock('sea_fragment');
				$this->tpl->setVariable('TXT_FRAGMENT',$this->getHighlighter()->getContent($this->getObjId(),$sub_item));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock('subitem');

			$this->tpl->setVariable('SEPERATOR',':');
			
			
			switch(ilLMObject::_lookupType($sub_item,$this->getObjId()))
			{
				case 'pg':
					$this->getItemListGUI()->setChildId($sub_item);
					$this->tpl->setVariable("SUBITEM_TYPE",$lng->txt('obj_pg'));
					$link = $this->getItemListGUI()->getCommandLink('page');
					$link .= ('&srcstring=1');
					$this->tpl->setVariable('LINK',$link);
					$this->tpl->setVariable('TARGET',$this->getItemListGUI()->getCommandFrame('page'));
					$this->tpl->setVariable('TITLE',ilLMObject::_lookupTitle($sub_item));			
					break;
					
				case 'st':
					
					$this->getItemListGUI()->setChildId($sub_item);
					$this->tpl->setVariable("SUBITEM_TYPE",$lng->txt('obj_st'));
					$link = $this->getItemListGUI()->getCommandLink('page');
					$link .= ('&srcstring=1');
					$this->tpl->setVariable('LINK',$link);
					$this->tpl->setVariable('TARGET',$this->getItemListGUI()->getCommandFrame('page'));
					$this->tpl->setVariable('TITLE',ilLMObject::_lookupTitle($sub_item));	
					break;

				default:

					if(ilObject::_lookupType($sub_item) != 'file')
					{
						return '';
					}
					
					$this->getItemListGUI()->setChildId('il__file_'.$sub_item);
					$this->tpl->setVariable('SUBITEM_TYPE',$lng->txt('obj_file'));
					$link = $this->getItemListGUI()->getCommandLink('downloadFile');
					$this->tpl->setVariable('LINK',$link);
					$this->tpl->setVariable('TITLE',ilObject::_lookupTitle($sub_item));
					break;
			}

			if(count($this->getSubItemIds(true)) > 1)
			{
				$this->parseRelevance($sub_item);
			}
			
			$this->tpl->parseCurrentBlock();
		}
		
		$this->showDetailsLink();
		
		return $this->tpl->get();	 
	}
}
?>