<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
 * Class ilMailTemplatesTableGUI
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
 * 
 * $Id$
 * 
 * @ilCtrl_isCalledBy ilMailTemplatesTableGUI: ilMailTemplatesGUI
 * 
 * @extends ilTable2GUI
 */
class ilMailTemplatesTableGUI extends ilTable2GUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ilCtrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/** 
	 * @var $ilDB ilDB
	 */
	protected $ilDB;

	/**
	 * @var array
	 */
	protected $filter = array();

	/**
	 * @param        $a_parent_obj
	 * @param string $a_parent_cmd
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $lng    ilLanguage
		 * @var $ilDB   ilDB
		 */
		global $ilCtrl, $lng, $ilDB;

		$this->ilCtrl = $ilCtrl;
		$this->lng    = $lng;
		$this->ilDB   = $ilDB;

		$this->setId('mailtpl' . $a_parent_obj->object->getId());

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setDefaultOrderDirection('ASC');
		$this->setDefaultOrderField('category_name');
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);

		$this->initFilter();
		$this->addColumns();

		$this->addCommandButtons();
		$this->addMultiCommands();

		$this->setFormAction($this->ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate('tpl.mail_templates_row_template.html', 'Services/MailTemplates');

		$this->setShowRowsSelector(true);
		$this->setSelectAllCheckbox('template_id[]');

		$this->setTitle($lng->txt('mail_templates'));

		$this->setFilterCommand('apply_template_type_filter');
		$this->setResetCommand('reset_template_type_filter');
	}

	/**
	 * 
	 */
	public function addColumns()
	{
		$this->addColumn('', '', '1%', true);
		$this->addColumn($this->lng->txt('mail_template_category'), 'category_name', '45%');
		$this->addColumn($this->lng->txt('mail_template_type'), 'template_type', '45%');
		$this->addColumn($this->lng->txt('actions'), '', '1px');
	}

	/**
	 * 
	 */
	private function addMultiCommands()
	{
		$this->addMultiCommand('confirm_delete_template_type', $this->lng->txt('delete'));
	}

	/**
	 * 
	 */
	private function addCommandButtons()
	{
		$this->addCommandButton('new_template_type', $this->lng->txt('new_template_type'));
	}

	/**
	 * @return string
	 */
	public function getOrderColumn()
	{
		$column = parent::getOrderColumn();

		if(!in_array(strtolower($column), array('category_name', 'template_type')))
		{
			return $this->getDefaultOrderField();
		}
		
		return $column;
	}

	/**
	 * @return string
	 */
	public function getOrderDirection()
	{
		$direction = parent::getOrderDirection();
		
		if(!in_array(strtolower($direction), array('asc', 'desc')))
		{
			return $this->getDefaultOrderDirection();
		}

		return $direction;
	}

	/**
	 * @return string
	 */
	private function buildWhereCondition()
	{
		$where = array();
		if(is_array($this->filter) && $this->filter)
		{
			foreach($this->filter as $key => $val)
			{
				if(!strlen($val))
				{
					continue;
				}

				switch($key)
				{
					case 'mail_template_category':
						$where[] = ' '.$this->ilDB->like(
							'category_name', 
							'text', 
							'%'.ilUtil::stripSlashes($val).'%', 
							false
						).' ';
						break;
				}
			}
		}
		
		if($where)
		{
			return ' WHERE '.implode(' AND ', $where); 
		}
		
		return '';
	}

	/**
	 * @return array
	 */
	private function getDataFromDb()
	{
		// @todo 4 mbecker: Move this to a data mapper class!!!!!!

		$query = "
			SELECT DISTINCT cat_mail_templates.id,
			(
				CASE
				WHEN message_subject IS NOT NULL THEN CONCAT(category_name, CONCAT(' | ', COALESCE(message_subject, '')))
				ELSE category_name
				END
			) category_name , template_type
			FROM cat_mail_templates
			LEFT JOIN cat_mail_variants ON mail_types_fi = cat_mail_templates.id
			{$this->buildWhereCondition()}
		";

		if($this->getLimit())
		{
			$this->ilDB->setLimit((int)$this->getLimit(), (int)$this->getOffset());
		}
		$res = $this->ilDB->query(
			"$query {$this->getOrderByPart()}"
		);
		
		$data = array();
		while($row = $this->ilDB->fetchAssoc($res))
		{
			$data[] = array(
				'id'            => $row['id'],
				'category_name' => $row['category_name'],
				'template_type' => $row['template_type']
			);
		}
		
		$res = $this->ilDB->query("SELECT COUNT(*) cnt FROM({$query}) subquery");
		$maxcount_data = $this->ilDB->fetchAssoc($res);

		return array('items' => $data, 'maxcount' => $maxcount_data['cnt']);
	}

	/**
	 * @return string
	 */
	private function getOrderByPart()
	{
		return ' ORDER BY ' . $this->getOrderColumn() . ' ' . $this->getOrderDirection();
	}

	/**
	 * Fill a single data row.
	 */
	protected function fillRow($a_set)
	{
		$action = new ilAdvancedSelectionListGUI();
		$action->setId('asl_' . $a_set['id']);
		$action->setListTitle($this->lng->txt('actions'));

		$this->tpl->setVariable('VAL_CHECKBOX', ilUtil::formCheckbox(false, 'template_id[]', $a_set['id']));
		$this->tpl->setVariable("VAL_CATEGORY", $a_set["category_name"]);
		$this->tpl->setVariable("VAL_TYPE", $a_set["template_type"]);

		$this->ilCtrl->setParameter($this->parent_obj, 'template_id', $a_set['id']);
		$action->addItem(
			$this->lng->txt('mail_template_settings'), 
			'', 
			$this->ilCtrl->getLinkTarget($this->parent_obj, 'show_template_type_settings')
		);
		
		$action->addItem(
			$this->lng->txt('mail_template_variants'), 
			'', 
			$this->ilCtrl->getLinkTarget($this->parent_obj, 'show_template_variants')
		);
		
		$this->ilCtrl->setParameter($this->parent_obj, 'template_id', '');
		$this->tpl->setVariable('VAL_ACTIONS', $action->getHtml());
	}

	/**
	 * Init filter
	 */
	public function initFilter()
	{
		include_once 'Services/Form/classes/class.ilTextInputGUI.php';
		$ti = new ilTextInputGUI($this->lng->txt('mail_template_category'), 'mail_template_category_filter');
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter['mail_template_category'] = $ti->getValue();
	}

	/**
	 * Get items
	 */
	public function fetchItems()
	{
		// Get table items and check filter
		// This is just an example, usually you call application classes
		// and pass the filter values.
		$this->determineOffsetAndOrder();
		$data = $this->getDataFromDB();
		$this->setData($data['items']);
		$this->setMaxCount($data['maxcount']);
	}
}
