<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');

/**
 * Class srModelObjectTableGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.5
 */
abstract class srModelObjectTableGUI extends ilTable2GUI {

	/**
	 * @var string
	 */
	protected $table_id = 'sr';
	/**
	 * @var string
	 */
	protected $table_title = 'Table (override protected $table_title)';
	/**
	 * @var string
	 */
	protected $table_prefix = 'srx';
	/**
	 * @var array
	 */
	protected $filter_array = array();
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	/**
	 * @var ilAccessHandler
	 */
	protected $access;
	/**
	 * @var int
	 */
	static $num = 0;


	/**
	 * @param        $a_parent_obj
	 * @param string $a_parent_cmd
	 */
	public function __construct($a_parent_obj, $a_parent_cmd) {
		global $ilCtrl, $ilTabs, $ilAccess;
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
		$this->access = $ilAccess;
		if ($this->initLanguage() === false) {
			global $lng;
			$this->lng = $lng;
		}
		$this->initTableProperties();
		$this->setId($this->table_id);
		$this->setTitle($this->table_title);
		parent::__construct($a_parent_obj, $a_parent_cmd);
		if ($this->initFormActionsAndCmdButtons() === false) {
			$this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
		}
		$this->initTableFilter();
		$this->initTableData();
		if ($this->initTableColumns() === false) {
			$this->initStandardTableColumns();
		}
		if ($this->initTableRowTemplate() === false) {
			$this->setRowTemplate('tpl.std_row_template.html', strstr(dirname(__FILE__), 'Customizing'));
		}
	}


	/**
	 * @return bool
	 * @description  returns false, if no filter is needed, otherwise implement filters
	 * @description  set custom metjosd for filtering and resetting ($this->setResetCommand('resetFilter'); and $this->setFilterCommand('applyFilter');)
	 */
	abstract protected function initTableFilter();


	/**
	 * @return void
	 * @description $this->setData(Your Array of Data)
	 */
	abstract protected function initTableData();


	/**
	 * @return bool
	 * @description returns false, if automatic columns are needed, otherwise implement your columns
	 */
	abstract protected function initTableColumns();


	/**
	 * @return bool
	 * @description returns false or set the following
	 * @description e.g. ovverride table id oder title: $this->table_id = 'myid', $this->table_title = 'My Title'
	 */
	abstract protected function initTableProperties();


	/**
	 * @return bool
	 * @description return false or implements own form action and
	 */
	abstract protected function initFormActionsAndCmdButtons();


	/**
	 * @return bool
	 * @description returns false, if dynamic template is needed, otherwise implement your own template by $this->setRowTemplate($a_template, $a_template_dir = "")
	 */
	abstract protected function initTableRowTemplate();


	/**
	 * @return bool
	 * @description returns false, if global language is needed; implement your own language by setting $this->lng
	 */
	abstract protected function initLanguage();


	/**
	 * @param $a_set
	 *
	 * @return bool
	 * @description implement your woen fillRow or return false
	 */
	abstract protected function fillTableRow($a_set);


	/**
	 * @param ilFormPropertyGUI $item
	 */
	final function addFilterItemToForm(ilFormPropertyGUI $item) {
		/**
		 * @var $item ilTextInputGUI
		 */
		$this->addFilterItem($item);
		$item->readFromSession();
		$this->filter_array[$item->getPostVar()] = $item->getValue();
	}


	/**
	 * @return bool
	 */
	final function initStandardTableColumns() {
		$data = $this->getData();
		if (count($data) === 0) {
			return false;
		}
		foreach (array_keys(array_shift($data)) as $key) {
			$this->addColumn($this->lng->txt($key), $key);
		}
		$this->addColumn($this->lng->txt('actions'), 'actions');

		return true;
	}


	/**
	 * @param array $a_set
	 *
	 * @internal    param array $_set
	 * @description override, when using own columns
	 */
	final function fillRow($a_set) {
		if ($this->fillTableRow($a_set) === false) {
			self::$num ++;
			foreach ($a_set as $value) {
				$this->addCell($value);
			}
			$this->ctrl->setParameter($this->parent_obj, 'object_id', $a_set['id']);
			$actions = new ilAdvancedSelectionListGUI();
			$actions->setId('actions_' . self::$num);
			$actions->setListTitle($this->lng->txt('actions'));
			$actions->addItem($this->lng->txt('edit'), 'edit', $this->ctrl->getLinkTarget($this->parent_obj, 'edit'));
			$actions->addItem($this->lng->txt('delete'), 'delete', $this->ctrl->getLinkTarget($this->parent_obj, 'confirmDelete'));
			$this->tpl->setCurrentBlock('cell');
			$this->tpl->setVariable('VALUE', $actions->getHTML());
			$this->tpl->parseCurrentBlock();
		}
	}


	/**
	 * @param $value
	 */
	public function addCell($value) {
		$this->tpl->setCurrentBlock('cell');
		$this->tpl->setVariable('VALUE', $value ? $value : '&nbsp;');
		$this->tpl->parseCurrentBlock();
	}


	/**
	 * @return mixed
	 */
	public function getNavStart() {
		return $this->getNavigationParameter('from');
	}


	/**
	 * @return mixed
	 */
	public function getNavStop() {
		return $this->getNavigationParameter('to');
	}


	/**
	 * @return mixed
	 */
	public function getNavSortField() {
		return $this->getNavigationParameter('sort_field');
	}


	/**
	 * @return mixed
	 */
	public function getNavorder() {
		return $this->getNavigationParameter('order');
	}


	/**
	 * @return array
	 */
	public function getNavigationParametersAsArray() {
		global $ilUser;
		/**
		 * @var $ilUser ilObjUser
		 */
		$hits = $ilUser->getPref('hits_per_page');
		$parameters = explode(':', $_GET[$this->getNavParameter()]);
		$return_values = array(
			'from' => $parameters[2] ? $parameters[2] : 0,
			'to' => $parameters[2] ? $parameters[2] + $hits - 1 : $hits - 1,
			'sort_field' => $parameters[0] ? $parameters[0] : false,
			'order' => $parameters[1] ? strtoupper($parameters[1]) : 'ASC'
		);

		return $return_values;
	}


	/**
	 * @param $param
	 *
	 * @return mixed
	 */
	public function getNavigationParameter($param) {
		$array = $this->getNavigationParametersAsArray();

		return $array[$param];
	}
}

?>