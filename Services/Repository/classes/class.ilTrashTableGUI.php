<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
 * TableGUI class for
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ingroup Services
 */
class ilTrashTableGUI extends ilTable2GUI
{
	protected const TABLE_BASE_ID = 'adm_trash_table';

	private $logger = null;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilObjectDefinition
	 */
	protected $obj_definition;


	/**
	 * @var int
	 */
	private $ref_id = 0;


	/**
	 * @var array
	 */
	private $current_filter = [];


	/**
	 * ilTrashTableGUI constructor.
	 * @param object $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param int $ref_id
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, int $ref_id)
	{
		global $DIC;

		$this->access = $DIC->access();
		$this->obj_definition = $DIC["objDefinition"];
		$this->ref_id = $ref_id;

		$this->logger = $DIC->logger()->rep();

		$this->setId(self::TABLE_BASE_ID);
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->lng->loadLanguageModule('rep');

	}

	/**
	 * Init table
	 */
	public function init()
	{
		$this->setTitle(
			$this->lng->txt('rep_trash_table_title') . ' "' .
			\ilObject::_lookupTitle(\ilObject::_lookupObjId($this->ref_id)) . '" '
		);

		$this->addColumn('','',1,1);
		$this->addColumn($this->lng->txt('type'),'type');
		$this->addColumn($this->lng->txt('title'),'title');
		$this->addColumn($this->lng->txt('rep_trash_table_col_deleted_by'),'deleted_by');
		$this->addColumn($this->lng->txt('rep_trash_table_col_deleted_on'),'deleted_on');
		$this->addColumn($this->lng->txt('rep_trash_table_col_num_subs'),'num_subs');

		$this->setDefaultOrderField('title');
		$this->setDefaultOrderField('asc');

		$this->setExternalSorting(false);
		$this->setExternalSegmentation(false);

		$this->setEnableHeader(true);
		$this->enable('sort');
		$this->setEnableTitle(true);
		$this->setEnableNumInfo(true);
		$this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));

		$this->setRowTemplate('tpl.trash_list_row.html', 'Services/Repository');
		$this->setSelectAllCheckbox('trash_id');

		$this->addMultiCommand('undelete' , $this->lng->txt('btn_undelete'));
		$this->addMultiCommand('confirmRemoveFromSystem', $this->lng->txt('btn_remove_system'));

		$this->initFilter();
	}

	/**
	 *
	 */
	public function initFilter()
	{
		$this->setDefaultFilterVisiblity(true);

		$type = new \ilMultiSelectInputGUI(
			$this->lng->txt('type'),
			'type'
		);
		$type->enableSelectAll(true);
		$type->setOptions($this->prepareTypeFilterTypes());
		$this->addFilterItem($type, false);
		$this->current_filter['type'] = $type->getValue();

		$title = $this->addFilterItemByMetaType(
			'title',
			\ilTable2GUI::FILTER_TEXT,
			false,
			$this->lng->txt('title')
		);
		$this->current_filter['title'] = $title->getValue();

		$deleted_by = $this->addFilterItemByMetaType(
			'deleted_by',
			\ilTable2GUI::FILTER_TEXT,
			false,
			$this->lng->txt('rep_trash_table_col_deleted_by')
		);
		$this->current_filter['deleted_by'] = $deleted_by->getValue();

		$deleted = $this->addFilterItemByMetaType(
			'deleted_on',
			\ilTable2GUI::FILTER_DATE_RANGE,
			false,
			$this->lng->txt('rep_trash_table_col_deleted_on')
		);
		$this->current_filter['deleted_on'] = $deleted->getValue();
	}

	/**
	 * Parse table
	 */
	public function parse()
	{
		$trash_tree_reader = new \ilTreeTrashQueries();
		$items = $trash_tree_reader->getTrashNodeForContainer($this->ref_id);

		$this->logger->dump($items);

		$rows = [];
		foreach($items as $item) {

			$row['id'] = $item->getRefId();
			$row['obj_id'] = $item->getObjId();
			$row['type'] = $item->getType();
			$row['title'] = $item->getTitle();
			$row['description'] = $item->getDescription();
			$row['deleted_by_id'] = $item->getDeletedBy();
			$row['deleted_by'] = $this->lng->txt('rep_trash_deleted_by_unknown');
			if($login = \ilObjUser::_lookupLogin($row['deleted_by_id'])) {
				$row['deleted_by'] = $login;
			}
			$row['deleted_on'] = $item->getDeleted();
			$row['num_subs'] = 0;

			$rows[] = $row;
		}


		$this->setMaxCount(count($rows));
		$this->setData($rows);
	}

	/**
	 * @inheritdoc
	 */
	protected function fillRow($row)
	{
		$this->tpl->setVariable('ID', $row['id']);
		$this->tpl->setVariable('VAL_TITLE' , $row['title']);
		if(strlen(trim($row['description']))) {
			$this->tpl->setCurrentBlock('with_desc');
			$this->tpl->setVariable('VAL_DESC', $row['description']);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock('with_path');
		$path = new ilPathGUI();
		$path->enableTextOnly(false);
		$this->tpl->setVariable('PATH', $path->getPath($this->ref_id, $row['id']));

		$this->logger->info($path->getPath($this->ref_id, $row['id']));

		$this->tpl->parseCurrentBlock();

		$img = \ilObject::_getIcon(
			$row['obj_id'],
			'small',
			$row['type']
		);
		if(strlen($img)) {
			$alt = ($this->obj_definition->isPlugin($row['type']))
				? $this->lng->txt('icon') . ' ' . \ilObjectPlugin::lookupTxtById($row['type'], 'obj_' . $row['type'])
				: $this->lng->txt('icon') . ' ' . $this->lng->txt('obj_' . $row['type'])
			;
			$this->tpl->setVariable('IMG_TYPE', \ilUtil::img($img, $alt));
		}

		$this->tpl->setVariable('VAL_DELETED_BY', $row['deleted_by']);

		$dt = new \ilDateTime($row['deleted_on'], IL_CAL_DATETIME);
		$this->tpl->setVariable('VAL_DELETED_ON', \ilDatePresentation::formatDate($dt));
		$this->tpl->setVariable('VAL_SUBS', (string) (int) $row['num_subs']);
	}

	/**
	 * @return array
	 */
	protected function prepareTypeFilterTypes()
	{
		$subs = $this->obj_definition->getSubObjectsRecursively(
			\ilObject::_lookupType($this->ref_id, true)
		);
		$options = [];
		foreach($subs as $type => $info) {

			if($type == 'rolf') {
				continue;
			}

			if(!$this->obj_definition->isRBACObject($type)) {
				continue;
			}
			$options[$type] = $this->lng->txt('objs_' . $type);
		}
		asort($options,SORT_LOCALE_STRING);
		return $options;
	}

}

?>
