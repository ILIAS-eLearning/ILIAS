<?php
/**
 * Created by JetBrains PhpStorm.
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Stefan Hecken <stefan.heclen@concepts-and-training.de>
 * Date: 20/06/13
 * Time: 11:12 AM
 * To change this template use File | Settings | File Templates.
 */

require_once("./Services/Form/classes/class.ilMultiSelectInputGUI.php");
require_once("./Services/User/classes/class.ilObjUser.php");
require_once("./Services/UICore/classes/class.ilTemplate.php");
/**
 * Class ilMultiSelectSearchInputGUI
 */
class ilMultiSelectSearchInputGUI extends ilMultiSelectInputGUI
{
	/**
	 * @var string
	 */
	protected $width;

	/**
	 * @var string
	 */
	protected $height;

	/**
	 * @var string
	 */
	protected $css_class;

	/**
	 * @var int
	 */
	protected $minimum_input_length = 0;

	/**
	 * @var string
	 */
	protected $ajax_link;

	/**
	 * @var ilTemplate
	 */
	protected $input_template;

	public function __construct($title, $post_var){
		global $tpl, $ilUser, $lng;
		if(substr($post_var, -2) != "[]")
			$post_var = $post_var."[]";
		parent::__construct($title, $post_var);

		$this->lng = $lng;
		$tpl->addJavaScript("./Services/TMS/Filter/lib/select2/js/select2.full.js");
		$tpl->addJavaScript("./Services/TMS/Filter/lib/select2/js/select2_locale_".$ilUser->getCurrentLanguage().".js");
		$tpl->addCss("./Services/TMS/Filter/lib/select2/css/select2.min.css");
		$this->setInputTemplate(new ilTemplate("tpl.multiple_select.html", true, true,"Services/TMS/Filter"));
		$this->setWidth("300px");
	}

	/**
	 * Check input, strip slashes etc. set alert, if input is not ok.
	 *
	 * @return	boolean		Input ok, true/false
	 */
	function checkInput()
	{
		global $lng;

		if ($this->getRequired() && count($this->getValue()) == 0)
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		return true;
	}

	public function getSubItems(){
		return array();
	}

	public function render(){
		global $lng;

		$tpl = $this->getInputTemplate();
		$values = $this->getValue();
		$options = $this->getOptions();

		$tpl->setVariable("POST_VAR", $this->getPostVar());

		$id = substr($this->getPostVar(), 0, -2);
		$tpl->setVariable("ID", $id);
		$id = str_replace("[","\\\[",$id);
		$id = str_replace("]","\\\]",$id);
		$tpl->setVariable("ID_SELECT", $id);
		$tpl->setVariable("WIDTH", $this->getWidth());
		$tpl->setVariable("HEIGHT", $this->getHeight());
		$tpl->setVariable("PLACEHOLDER", $lng->txt("please_choose"));
		$tpl->setVariable("MINIMUM_INPUT_LENGTH", $this->getMinimumInputLength());
		$tpl->setVariable("CSS_CLASS", $this->getCssClass());

		if(isset($this->ajax_link)){
			$tpl->setVariable("AJAX_LINK", $this->getAjaxLink());
		}

		if($this->getDisabled())
			$tpl->setVariable("ALL_DISABLED", "disabled=\"disabled\"");

		if($options)
		{
			foreach($options as $option_value => $option_text)
			{
				$tpl->setCurrentBlock("item");
				if ($this->getDisabled())
				{
					$tpl->setVariable("DISABLED",
						" disabled=\"disabled\"");
				}
				if (in_array($option_value, $values))
				{
					$tpl->setVariable("SELECTED",
						"selected");
				}

				$tpl->setVariable("VAL", ilUtil::prepareFormOutput($option_value));
				$tpl->setVariable("TEXT", $option_text);
				$tpl->parseCurrentBlock();
			}
		}
		return $tpl->get();
	}

	/**
	 * @deprecated setting inline style items from the controller is bad practice. please use the setClass together with an appropriate css class.
	 * @param string $height
	 */
	public function setHeight($height)
	{
		$this->height = $height;
	}

	/**
	 * @return string
	 */
	public function getHeight()
	{
		return $this->height;

	}

	/**
	 * @deprecated setting inline style items from the controller is bad practice. please use the setClass together with an appropriate css class.
	 * @param string $width
	 */
	public function setWidth($width)
	{
		$this->width = $width;
	}

	/**
	 * @return string
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @param string $css_class
	 */
	public function setCssClass($css_class)
	{
		$this->css_class = $css_class;
	}

	/**
	 * @return string
	 */
	public function getCssClass()
	{
		return $this->css_class;
	}

	/**
	 * @param int $minimum_input_length
	 */
	public function setMinimumInputLength($minimum_input_length)
	{
		$this->minimum_input_length = $minimum_input_length;
	}

	/**
	 * @return int
	 */
	public function getMinimumInputLength()
	{
		return $this->minimum_input_length;
	}

	/**
	 * @param string $ajax_link setting the ajax link will lead to ignoration of the "setOptions" function as the link given will be used to get the
	 */
	public function setAjaxLink($ajax_link)
	{
		$this->ajax_link = $ajax_link;
	}

	/**
	 * @return string
	 */
	public function getAjaxLink()
	{
		return $this->ajax_link;
	}

	/**
	 * @param \srDefaultAccessChecker $access_checker
	 */
	public function setAccessChecker($access_checker)
	{
		$this->access_checker = $access_checker;
	}/**
	 * @return \srDefaultAccessChecker
	 */
	public function getAccessChecker()
	{
		return $this->access_checker;
	}

	/**
	 * @param \ilTemplate $input_template
	 */
	public function setInputTemplate($input_template)
	{
		$this->input_template = $input_template;
	}

	/**
	 * @return \ilTemplate
	 */
	public function getInputTemplate()
	{
		return $this->input_template;
	}

	/**
	 * This implementation might sound silly. But the multiple select input used parses the post vars differently if you use ajax. thus we have to do this stupid "trick". Shame on select2 project ;)
	 * @return string the real postvar.
	 */
	protected function searchPostVar(){
		if(substr($this->getPostVar(), -2) == "[]")
			return substr($this->getPostVar(), 0 , -2);
		else
			return $this->getPostVar();
	}

	public function setValueByArray($array){
		$val = $array[$this->searchPostVar()];
		if(is_array($val))
			$val;
		elseif(!$val)
			$val =  array();
		else
			$val = explode(",", $val);
		$this->setValue($val);
	}
}
