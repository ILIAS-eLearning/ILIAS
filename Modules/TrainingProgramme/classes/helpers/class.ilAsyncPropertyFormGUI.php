<?php
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * Class ilAsyncPropertyFormGUI
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilAsyncPropertyFormGUI extends ilPropertyFormGUI {
	protected $has_errors = false;

	public static $js_path = "./Modules/TrainingProgramme/templates/js/";
	public static $default_name = "async_form";
	public static $on_load_set = false;

	public $is_async = true;

	public function __construct(array $config = array(), $is_async = true) {
		parent::__construct();

		foreach($config as $key=>$value) {
			$setterMethod = "set".ucfirst($key);
			if(method_exists($this, $setterMethod)) {
				$setterMethod($value);
			}
		}

		$this->setAsync($is_async);
		$this->setName(self::$default_name);

		self::initJs($this->getJsPath());
	}

	public static function initJs($add_on_load = false, $js_base_path = null) {
		global $tpl, $ilCtrl;

		$js_path = (isset($js_base_path))? $js_base_path : self::$js_path;

		$tpl->addJavaScript($js_path.'ilAsyncPropertyFormGUI.js');
		$tpl->addOnLoadCode("$.ilAsyncPropertyForm.global_config.error_message_template = '".self::getErrorMessageTemplate()."';");

		if($add_on_load && !self::$on_load_set) {
			$tpl->addOnLoadCode('$("body").ilAsyncPropertyForm();');
			self::$on_load_set = true;
		}

		//$tpl->addOnLoadCode('$("form[name=\''.self::$default_name.'\']").ilAsyncPropertyForm();');
		//var_dump(self::getErrorMessageTemplate());
	}

	public function checkInput() {
		$result = parent::checkInput();
		$this->has_errors = $result;

		return $result;
	}

	public function getName() {
		return parent::getName();
	}

	public function getErrors() {
		if(!$this->check_input_called) {
			$this->checkInput();
		}

		$errors = array();
		foreach($this->getItems() as $item) {
			if($item->getAlert() != "") {
				$errors[] = array('key'=>$item->getFieldId(), 'message'=>$item->getAlert());
			}
		}
		return $errors;
	}

	/**
	 * @return boolean
	 */
	public function hasErrors() {
		return $this->has_errors;
	}

	public function getErrorMessageTemplate() {
		global $lng;

		$tpl = new ilTemplate("tpl.property_form.html", true, true, "Services/Form");

		$tpl->setCurrentBlock("alert");
		$tpl->setVariable("IMG_ALERT", ilUtil::getImagePath("icon_alert.svg"));
		$tpl->setVariable("ALT_ALERT", $lng->txt("alert"));
		$tpl->setVariable("TXT_ALERT", "[TXT_ALERT]");
		$tpl->parseCurrentBlock();
		$content = trim($tpl->get("alert"));

		return $content;
	}

	public function isSubmitted() {
		if(isset($_POST['cmd'])) {
			return true;
		}
		return false;
	}

	function setFormAction($a_formaction) {
		if($this->isAsync()) {
			$a_formaction .= "&cmdMode=asynch";
		}

		$this->formaction = $a_formaction;
	}

	/**
	 * @return mixed
	 */
	public function getJsPath() {
		return $this->js_path;
	}

	/**
	 * @param mixed $js_path
	 */
	public function setJsPath($js_path) {
		$this->js_path = $js_path;
	}

	/**
	 * @return mixed
	 */
	public function getDefaultClass() {
		return $this->default_class;
	}

	/**
	 * @return boolean
	 */
	public function isAsync() {
		return $this->is_async;
	}


	/**
	 * @param boolean $is_async
	 */
	public function setAsync($is_async) {
		$this->is_async = $is_async;
	}


	/**
	 * @param mixed $default_class
	 */
	public function setDefaultClass($default_class) {
		$this->default_class = $default_class;
	}

	public function cloneForm(ilPropertyFormGUI $form_to_clone) {
		if(count($this->getItems()) > 0) {
			throw new ilException("You cannot clone into a already filled form!");
		}

		$reflect = new ReflectionClass($this);
		$properties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

		foreach($properties as $property) {
			$this->{$property->getName()} = $property->getValue($form_to_clone);
		}

		foreach($form_to_clone->getItems() as $item) {
			$this->addItem($item);
		}

		foreach($form_to_clone->getCommandButtons() as $button) {
			$this->addCommandButton($button['cmd'], $button['text']);
		}

		return $this;
	}

	public function getHTML() {
		global $tpl;

		if($this->isAsync() && !self::$on_load_set) {
			$tpl->addOnLoadCode('$("body").ilAsyncPropertyForm();');
			self::$on_load_set = true;
		}

		return parent::getHTML();
	}
}