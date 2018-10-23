<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesMetaData
 */

class ilMDCopyrightUsageTableGUI extends ilTable2GUI
{
	/**
	 * @var integer
	 */
	protected $copyright_id;

	protected $db;

	protected $filter;

	/**
	 * ilCopyrightUsageGUI constructor.
	 * @param $a_parent_obj ilMDCopyrightUsageGUI
	 * @param $a_parent_cmd string
	 */
	public function __construct($a_parent_obj, $a_parent_cmd='')
	{
		global $DIC;

		$this->db = $DIC->database();

		$this->copyright_id = $a_parent_obj->getEntryId();

		$this->setId("mdcopusage".$this->copyright_id);

		$md_entry = new ilMDCopyrightSelectionEntry($this->copyright_id);

		$this->setTitle($md_entry->getTitle());

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->addColumn($this->lng->txt('object'),'object');
		$this->addColumn($this->lng->txt('meta_references'),'references');
		$this->addColumn($this->lng->txt('meta_sub_items'),'subitems');
		$this->addColumn($this->lng->txt('owner'),'owner');

		$this->setRowTemplate("tpl.show_copyright_usages_row.html","Services/MetaData");
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->setResetCommand("resetUsageFilter");
		$this->setFilterCommand("applyUsageFilter");

		$this->setDisableFilterHiding(true);
		$this->initFilter();

		$data = $this->collectData($this->getCurrentFilter());
		$this->setData($data);

	}

	function initFilter()
	{
		ilLoggerFactory::getRootLogger()->debug("**** INIT FILTER ****");

		$title = $this->addFilterItemByMetaType(
			"title",
			ilTable2GUI::FILTER_TEXT,
			false,
			$this->lng->txt("object")." ".$this->lng->txt("title")
		);
		$this->filter["title"] = $title->getValue();
	}

	/**
	 * Get current filter settings
	 * @return	array
	 */
	function getCurrentFilter()
	{
		ilLoggerFactory::getRootLogger()->debug("***** GET CURRENT FILTER *****");
		$filter = array();
		if($this->filter["title"])
		{
			$filter["title"] = $this->filter["title"];
		}
		return $filter;
	}

	function fillRow($a_set)
	{
		$this->tpl->setVariable('TITLE',$a_set['title']);
		$this->tpl->setVariable("DESCRIPTION", $a_set['desc']);
		if($a_set['references'])
		{
			include_once('./Services/Tree/classes/class.ilPathGUI.php');
			$path = new ilPathGUI();
			$path->enableDisplayCut(true);
			$path->enableTextOnly(false);

			foreach($a_set['references'] as $reference)
			{
				$this->tpl->setCurrentBlock("references");
				$this->tpl->setVariable("REFERENCE",$path->getPath(ROOT_FOLDER_ID, $reference));
				$this->tpl->parseCurrentBlock();
			}
		}

		$this->tpl->setVariable('SUB_ITEMS',$a_set['sub_items']);

		//TODO FIX WHITE PAGE OWNER LINK
		if($a_set['owner_link'])
		{
			$this->tpl->setCurrentBlock("link_owner");
			$this->tpl->setVariable("OWNER_LINK", $a_set['owner_link']);
			$this->tpl->setVariable('OWNER',$a_set['owner_name']);
			$this->tpl->parseCurrentBlock();
		} else {
			$this->tpl->setCurrentBlock("owner");
			$this->tpl->setVariable('OWNER',$a_set['owner_name']);
			$this->tpl->parseCurrentBlock();
		}

	}

	function collectData(array $filters)
	{
		ilLoggerFactory::getRootLogger()->debug("******* COLLECT DATA *****");
		$db_data = $this->getDataFromDB();

		ilLoggerFactory::getRootLogger()->dump($db_data);
		$data = array();

		foreach($db_data as $item)
		{
			ilLoggerFactory::getRootLogger()->debug("-foreach loop data");
			$add_data = true;
			$obj_id = $item['obj_id'];
			if($filters['title'])
			{
				ilLoggerFactory::getRootLogger()->debug("------ FILTER BY TITLE -----");
				ilLoggerFactory::getRootLogger()->debug("title = ".ilObject::_lookupTitle($obj_id));
				ilLoggerFactory::getRootLogger()->debug("filter title = ".$filters['title']);
				if(stripos(ilObject::_lookupTitle($obj_id),$filters['title']) === false)
				{
					ilLoggerFactory::getRootLogger()->debug("no mostramos este => ".ilObject::_lookupTitle($obj_id));
					$add_data = false;
				}
			}
			if($add_data)
			{
				ilLoggerFactory::getRootLogger()->debug("++++  mostramos este => ".ilObject::_lookupType($obj_id));
				$data[] = array(
					"obj_id" => $obj_id,
					"type" => ilObject::_lookupType($obj_id),
					"title" => ilObject::_lookupTitle($obj_id),
					"desc" => ilObject::_lookupDescription($obj_id),
					"references" => ilObject::_getAllReferences($obj_id),
					"owner_name" => ilUserUtil::getNamePresentation(ilObject::_lookupOwner($obj_id)),
					"owner_link" => ilUserUtil::getProfileLink(ilObject::_lookupOwner($obj_id)),
					"sub_items" => $this->getCountSubItemsFromDB($obj_id)
				);
			}

		}
		
		return $data;
	}

	public function getDataFromDB()
	{
		$query = "SELECT rbac_id, obj_id, obj_type FROM il_meta_rights ".
			"WHERE description = ".$this->db->quote('il_copyright_entry__'.IL_INST_ID.'__'.$this->copyright_id,'text').
			" GROUP BY rbac_id";

		$result = $this->db->query($query);
		$data = array();
		while ($row = $this->db->fetchAssoc($result))
		{
			$data[] = array(
				"obj_id" =>$row['rbac_id'],
				"obj_type" => $row['obj_type']
			);
		}
		ilLoggerFactory::getRootLogger()->debug("DATA FROM DB");
		ilLoggerFactory::getRootLogger()->dump($data);
		return $data;
	}

	public function getCountSubItemsFromDB($a_rbac_id)
	{
		$query = "SELECT count(rbac_id) total FROM il_meta_rights ".
			"WHERE rbac_id = ".$this->db->quote($a_rbac_id).
			" AND rbac_id <> obj_id";

		$result = $this->db->query($query);
		$row = $this->db->fetchAssoc($result);

		return $row['total'];
	}
}