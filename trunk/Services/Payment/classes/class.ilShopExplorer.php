<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");
#include_once ("./Services/Repository/classes/class.ilRepositoryExplorer.php");
include_once './Services/Payment/classes/class.ilPaymentObject.php';

/**
* Class ilShopExplorer
* class for explorer view 
* 

* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id:$
* 
* @ingroup ServicesPayment
*/

// UNUSED ?!
class ilShopExplorer extends ilExplorer
{
	/**
	* id of root node
	* @var int root_id
	* @access private
	*/
	public $root_id;

	public $target = null;


	/**
	 *
	 * @var array ref_ids
	 */
	public $payment_objects = array();


	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	*/
    public function ilShopExplorer($tpl, $a_target, $a_ref_id)
	{
		global $tree;
		
		$this->tree = $tree;
		$this->tpl = $tpl;
		$this->target = $a_target;
		
		$this->payment_objects = ilPaymentObject::getAllBuyableObjects();

		$this->tpl->addCss('./Services/Payment/css/shop_tree.css');

		$this->addFilter("root");
		$this->addFilter("cat");
		$this->addFilter('catr');
		$this->addFilter("grp");
		$this->addFilter("icrs");
		$this->addFilter("crs");
		$this->addFilter('crsr');
		$this->addFilter('rcrs');
		$this->addFilter('file');
		$this->addFilter('tst');
		$this->addFilter('exc');
		$this->setFiltered(true);
		$this->setFilterMode(IL_FM_NEGATIVE);

	}

	public function renderTree()
	{
		$this->setOutput(0);
	}

	/**
	* Creates output for explorer view in admin menue
	* recursive method
	* @access	public
	* @param	integer		parent_node_id where to start from (default=0, 'root')
	* @param	integer		depth level where to start (default=1)
	* @return	string
	*/
	public function setOutput($a_parent, $a_depth = 1)
	{
		global $ilCtrl, $tree;
		
		$this->root_id = $tree->getRootId();

		if($a_parent < $a_depth)
		{
			foreach($this->payment_objects as $ref_id)
			{
				$objects = $tree->getPathFull($ref_id);

				foreach($objects as $object)
				{
					if($object['child'] == $this->root_id)
					{
						$href = $ilCtrl->getLinkTargetByClass('ilshopgui','');

						$title ="<span style='white-space:wrap;' class='frmTitle'><a class='small' href='".$href."'>"
							.stripslashes($object['title'])."</a></span>"
							."<div style='white-space:nowrap; margin-bottom:5px;' class='small'></div>";
						
						$this->tpl->setVariable('TREE_ROOT_NODE_VARIABLE', 'Node'.$object['child']);
						$this->tpl->setVariable('TREE_ROOT_NODE_LINK', $title);

						// Recursive
						$this->setOutput($object['child'], $a_depth);
					}
					else
					{
						if($this->checkFilter($object['type']) && ilPaymentObject::_isBuyable($object['ref_id']))
						{
		
						}
						else
						if($this->checkFilter($object['type']) && !ilPaymentObject::_isBuyable($object['ref_id']))
						{
							$href = $ilCtrl->getLinkTargetByClass('ilshopgui','');
							$title ="<span style='white-space:wrap;' class='frmTitle'><a class='small' href='".$href."&tree_ref_id=".$object['child']."'>"
							.stripslashes($object['title'])."</a></span>"
							."<div style='white-space:nowrap; margin-bottom:5px;' class='small'></div>";

							$this->tpl->setCurrentBlock('nodes');
							$this->tpl->setVariable('NODES_VARNAME', 'Node'.$object['child']);
							$this->tpl->setVariable('NODES_PARENT_VARNAME', 'Node'.$object['parent']);
							$this->tpl->setVariable('NODES_LINK', $title);
							$this->tpl->parseCurrentBlock();

							// Recursive
							$this->setOutput($object['child'], $a_depth);
						}
					}
				} //foreach
			}
		}
	} //function
	

	public function getOutput()
	{
		return true;
		
	}
	
} // END class.ilExplorer
?>
