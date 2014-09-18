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
* Creates a path for a start and endnode
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesTree
*/
class ilPathGUI
{
	private $startnode = ROOT_FOLDER_ID;
	private $endnode = ROOT_FOLDER_ID;
	
	private $textOnly = true;
	private $useImages = false;
	private $hide_leaf = true;
	private $display_cut = false;
	
	protected $lng = null;
	protected $tree = null;
	
	/**
	 * Constructor 
	 */
	public function __construct()
	{
		global $tree,$lng;
		
		$this->tree = $tree;
		$this->lng = $lng;
		
	}
	
	/**
	 * get path 
	 * @param int	$a_startnode	ref_id of startnode
	 * @param int	$a_endnode		ref_id of endnode
	 * @return string html
	 */
	public function getPath($a_startnode,$a_endnode)
	{
		$this->startnode = $a_startnode;
		$this->endnode = $a_endnode;
		
		return $this->getHTML();
	}
	
	/**
	 * render path as text only 
	 * @param	bool $a_text_only	path as text only true/false
	 * @return
	 */
	public function enableTextOnly($a_status)
	{
		$this->textOnly = $a_status;
	}
	
	/**
	 * show text only 
	 * @return
	 */
	public function textOnly()
	{
		return $this->textOnly;
	}
	
	/**
	 * Hide leaf node in path
	 * @param type $a_status
	 */
	public function enableHideLeaf($a_status)
	{
		$this->hide_leaf = $a_status;
	}
	
	public function hideLeaf()
	{
		return $this->hide_leaf;
	}
	/**
	 * set use images 
	 * @param	bool
	 * @return
	 */
	public function setUseImages($a_status)
	{
		$this->useImages = $a_status;
	}
	
	/**
	 * get use images 
	 * @return
	 */
	public function getUseImages()
	{
		return $this->useImages;
	}

	/**
	 * Display a cut with "..."
	 * @param $a_status bool
	 */
	public function enableDisplayCut($a_status)
	{
		$this->display_cut = $a_status;
	}

	/**
	 * Display a cut with "..."
	 * @return bool
	 */
	public function  displayCut()
	{
		return $this->display_cut;
	}
	
	/**
	 * get html 
	 * @return
	 */
	protected function getHTML()
	{
		if($this->textOnly())
		{
			$tpl = new ilTemplate('tpl.locator_text_only.html',true,true, "Services/Locator");
			
			$first = true;

			// Display cut
			if($this->displayCut() && $this->startnode != ROOT_FOLDER_ID)
			{
				$tpl->setCurrentBlock('locator_item');
				$tpl->setVariable('ITEM',"...");
				$tpl->parseCurrentBlock();

				$first = false;
			}

			foreach($this->getPathIds() as $ref_id)
			{
				$obj_id = ilObject::_lookupObjId($ref_id);
				$title = ilObject::_lookupTitle($obj_id);
				
				if($first)
				{
					if($ref_id == ROOT_FOLDER_ID)
					{
						$title = $this->lng->txt('repository');
					}
				}
				else
				{
					$tpl->touchBlock('locator_separator_prefix');
				}

				$tpl->setCurrentBlock('locator_item');
				$tpl->setVariable('ITEM',$title);
				$tpl->parseCurrentBlock();
				$first = false;
			}
			return $tpl->get();
		}
		else
		{
			// With images and links
			include_once './Services/Link/classes/class.ilLink.php';
			
			$tpl = new ilTemplate('tpl.locator.html',true,true,'Services/Locator');
			
			$first = true;

			// Display cut 
			if($this->displayCut() && $this->startnode != ROOT_FOLDER_ID)
			{
				$tpl->setCurrentBlock('locator_item');
				$tpl->setVariable('ITEM',"...");
				$tpl->parseCurrentBlock();

				$first = false;
			}

			foreach($this->getPathIds() as $ref_id)
			{
				$obj_id = ilObject::_lookupObjId($ref_id);
				$title = ilObject::_lookupTitle($obj_id);
				$type = ilObject::_lookupType($obj_id);
				
				if($first)
				{
					if($ref_id == ROOT_FOLDER_ID)
					{
						$title = $this->lng->txt('repository');
					}
				}
				else
				{
					$tpl->touchBlock('locator_separator_prefix');
				}
				if($this->getUseImages())
				{
					$tpl->setCurrentBlock('locator_img');
					$tpl->setVariable('IMG_SRC',ilUtil::getTypeIconPath($type,$obj_id));
					$tpl->setVariable('IMG_ALT',$this->lng->txt('obj_'.$type));
					$tpl->parseCurrentBlock();
				}				
				$tpl->setCurrentBlock('locator_item');
				$tpl->setVariable('LINK_ITEM',ilLink::_getLink($ref_id,$type));
				$tpl->setVariable('ITEM',$title);
				$tpl->parseCurrentBlock();
				$first = false;
			}
			return $tpl->get();
		}
	}
	
	/**
	 * 
	 * @param
	 * @return
	 */
	protected function getPathIds()
	{
		$path = $this->tree->getPathId($this->endnode,$this->startnode);
		if($this->hideLeaf())
		{
			unset($path[count($path) - 1]);
		}
		return $path ? $path : array();
	}
}
?>
