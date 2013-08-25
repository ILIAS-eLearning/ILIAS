<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Organisation Unit Tree
*
* @author	Bjoern Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup ServicesOrgUnit
*
*/
class ilOrgUnitTreeExplorerGUI
{
	private $tpl = null;

	public function __construct(ilOrgUnit $a_root_unit)
	{
		$this->root_unit = $a_root_unit;

		$this->tpl = new ilTemplate('tpl.org_unit_tree_explorer.html', true, true, 'Services/OrgUnit');
	}

	private function renderUnit(ilOrgUnit $a_unit, $a_depth)
	{
		global $lng;

		$a_unit->initAssigns();
		
		$this->tpl->setCurrentBlock('exp_item_begin');
		$this->tpl->setVariable('CLASSNAME', 'depth_'.$a_depth);
		$this->tpl->parseCurrentBlock();

		for($i = 1; $i < $a_depth; $i++)
		{
			$this->tpl->setCurrentBlock('exp_item_indent');
			$this->tpl->touchBlock('exp_item_indent');
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock('exp_item_icon');
		$this->tpl->setVariable('ITEM_ICON_SRC', ilUtil::getImagePath('icon_root_s.png'));
		$this->tpl->parseCurrentBlock();

		if($a_unit->getId() == ilOrgUnitTree::ROOT_UNIT_ID)
		{
			$this->tpl->setCurrentBlock('exp_item_title');
			$this->tpl->setVariable('ITEM_TITLE', $lng->txt('org_unit_tree_root'));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock('exp_item_title');
			$this->tpl->setVariable('ITEM_TITLE', $a_unit->getTitle());
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock('exp_item_subtitle');
			$this->tpl->setVariable('ITEM_SUBTITLE', $a_unit->getSubTitle());
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock('explorer');
		$this->tpl->parseCurrentBlock();

		#$assigned_users = $a_unit->getAssignedUsers();
		$assigned_users = array();
		if( count($assigned_users) )
		{
			$this->tpl->setCurrentBlock('exp_list_begin');
			$this->tpl->setVariable('CLASSNAME', 'depth_'.($a_depth + 1));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock('explorer');
			$this->tpl->parseCurrentBlock();

			foreach($assigned_users as $user_id => $properties)
			{
				$user = ilObjectFactory::getInstanceByObjId($user_id);

				$this->tpl->setCurrentBlock('exp_item_begin');
				$this->tpl->setVariable('CLASSNAME', 'depth_'.($a_depth + 1));
				$this->tpl->parseCurrentBlock();

				for($i = 1; $i < ($a_depth + 1); $i++)
				{
					$this->tpl->setCurrentBlock('exp_item_indent');
					$this->tpl->touchBlock('exp_item_indent');
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock('exp_item_icon');
				$this->tpl->setVariable('ITEM_ICON_SRC', ilUtil::getImagePath('icon_usr_s.png'));
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock('exp_item_title');
				$this->tpl->setVariable('ITEM_TITLE', $user->getLastName().', '.$user->getFirstName());
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock('exp_item_end');
				$this->tpl->touchBlock('exp_item_end');
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock('explorer');
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock('exp_list_end');
			$this->tpl->touchBlock('exp_list_end');
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock('explorer');
			$this->tpl->parseCurrentBlock();
		}

		if( $a_unit->hasChilds() )
		{
			$this->tpl->setCurrentBlock('exp_list_begin');
			$this->tpl->setVariable('CLASSNAME', 'depth_'.($a_depth + 1));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock('explorer');
			$this->tpl->parseCurrentBlock();

			foreach($a_unit->getChilds() as $child_unit)
			{
				$this->renderUnit( $child_unit, ($a_depth + 1) );
			}

			$this->tpl->setCurrentBlock('exp_list_end');
			$this->tpl->touchBlock('exp_list_end');
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock('explorer');
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock('exp_item_end');
		$this->tpl->touchBlock('exp_item_end');
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock('explorer');
		$this->tpl->parseCurrentBlock();
	}

	public function getHTML()
	{
		$depth = 1;

		$this->tpl->setCurrentBlock('exp_list_begin');
		$this->tpl->setVariable('CLASSNAME', 'depth_'.$depth);
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock('explorer');
		$this->tpl->parseCurrentBlock();

		$this->renderUnit($this->root_unit, $depth);

		$this->tpl->setCurrentBlock('exp_list_end');
		$this->tpl->touchBlock('exp_list_end');
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock('explorer');
		$this->tpl->parseCurrentBlock();

		$html = $this->tpl->get();

		return $html;
	}
}

?>
