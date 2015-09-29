<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php';

/**
 * ilForumMoveTopicsExplorer
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumMoveTopicsExplorer extends ilRepositorySelectorExplorerGUI
{
	/**
	 * @var int
	 */
	protected $current_frm_ref_id = 0;

	/**
	 * {@inheritdoc}
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_selection_gui = null, $a_selection_cmd = "selectObject",
								$a_selection_par = "sel_ref_id")
	{
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_selection_gui, $a_selection_cmd, $a_selection_par);
		$this->setTypeWhiteList(array('root', 'cat', 'fold', 'crs', 'grp', 'frm'));
		$this->setSelectMode('frm_ref_id');
	}

	/**
	 * @return int
	 */
	public function getCurrentFrmRefId()
	{
		return $this->current_frm_ref_id;
	}

	/**
	 * @param int $current_frm_ref_id
	 */
	public function setCurrentFrmRefId($current_frm_ref_id)
	{
		$this->current_frm_ref_id = $current_frm_ref_id;
	}

	/**
	 * {@inheritdoc}
	 */
	function isNodeVisible($a_node)
	{
		return parent::isNodeVisible($a_node);
	}

	/**
	 * {@inheritdoc}
	 */
	function renderNode($a_node, $tpl)
	{
		$this->select_postvar = $this->isNodeClickable($a_node) ? 'frm_ref_id' : '';
		parent::renderNode($a_node, $tpl);
	}

	/**
	 * {@inheritdoc}
	 */
	function isNodeClickable($a_node)
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 */
		global $ilAccess;

		if($a_node['type'] == 'frm')
		{
			if($this->getCurrentFrmRefId() && $this->getCurrentFrmRefId() == $a_node['child'])
			{
				return false;
			}

			if(!$ilAccess->checkAccess('read', '', $a_node['child']))
			{
				return false;
			}

			$is_valid_type = true;
			if(is_array($this->getClickableTypes()) && count($this->getClickableTypes()) > 0)
			{
				$is_valid_type = in_array($a_node['type'], $this->getClickableTypes());
			}

			return $ilAccess->checkAccess('moderate_frm', '', $a_node['child']) && $is_valid_type;
		}

		return false;
	}
}