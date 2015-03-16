<?php
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * Class ilAsyncPropertyFormGUI
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilAsyncPropertyFormGUI extends ilPropertyFormGUI {
	protected static $js_path = "./Modules/TrainingProgramme/templates/js/";
	protected static $default_from_name = "async_form";
	protected static $js_on_load_added = array();

	protected $has_errors = false;
	protected $is_async = true;

	public function __construct(array $config = array(), $is_async = true) {
		parent::__construct();

		foreach($config as $key=>$value) {
			$setterMethod = "set".ucfirst($key);
			if(method_exists($this, $setterMethod)) {
				$setterMethod($value);
			}
		}

		$this->setAsync($is_async);
		$this->setName(self::$default_from_name);
	}


	/**
	 * Adds all needed js
	 * By default is called by ilAsyncPropertyFormGUI::getHTML()
	 *
	 * @param bool $add_form_loader
	 * @param null $js_base_path
	 */
	public static function initJs($add_form_loader = false, $js_base_path = null) {
		global $tpl;

		$js_path = (isset($js_base_path))? $js_base_path : self::$js_path;

		$tpl->addJavaScript($js_path.'ilAsyncPropertyFormGUI.js');

		$global_config = "$.ilAsyncPropertyForm.global_config.error_message_template = '".self::getErrorMessageTemplate()."'; $.ilAsyncPropertyForm.global_config.async_form_name = '".self::$default_from_name."';";
		self::addOnLoadCode('global_config', $global_config);

		if($add_form_loader) {
			self::addOnLoadCode('form_loader', '$("body").ilAsyncPropertyForm();');
		}
	}


	/**
	 * Saves the change input result into a property
	 *
	 * @return bool
	 */
	public function checkInput() {
		$result = parent::checkInput();
		$this->has_errors = $result;

		return $result;
	}

	/**
	 * Return errors of the form as array
	 *
	 * @return array array with field id and error message: array([]=>array('key'=>fieldId, 'message'=>error-message))
	 */
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
	 * Return if there were errors on the last checkInput call
	 *
	 * @return boolean
	 */
	public function hasErrors() {
		return $this->has_errors;
	}


	/**
	 * Returns the error-message template for the client-side validation
	 *
	 * @return string
	 */
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

	/**
	 * Copies form items, buttons and properties from another form
	 *
	 * @param ilPropertyFormGUI $form_to_clone
	 *
	 * @return $this
	 * @throws ilException
	 */
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


	/**
	 * Adds onload code to the template
	 *
	 * @param $id
	 * @param $content
	 */
	protected function addOnLoadCode($id, $content) {
		global $tpl;

		if(!isset(self::$js_on_load_added[$id])) {
			$tpl->addOnLoadCode($content);
			self::$js_on_load_added[$id] = $content;
		}
	}

	/**
	 * Returns the rendered form content
	 *
	 * @return string
	 */
	public function getHTML() {
		self::initJs($this->isAsync());

		return parent::getHTML();
	}

	/**
	 * Checks if the form was submitted
	 *
	 * @return bool
	 */
	public function isSubmitted() {
		if(isset($_POST['cmd'])) {
			return true;
		}
		return false;
	}


	/**
	 * Sets the form action
	 * If the form is set to async, the cmdMode=asynch is added to the url
	 *
	 * @param string $a_formaction
	 */
	public function setFormAction($a_formaction) {
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
	public function getDefaultFormName() {
		return self::$default_from_name;
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
	 * @param string $a_name
	 */
	public function setName($a_name) {
		self::$default_from_name = $a_name;

		parent::setName($a_name);
	}
}