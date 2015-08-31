<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php';

/**
 * Class ilManualPlaceholderInputGUI
 * @author Nadia Ahmad <nahmad@databay.de> 
 */
class ilManualPlaceholderInputGUI extends ilSubEnabledFormPropertyGUI
{
	/**
	 * @var array
	 */
	protected $placeholders = array();

	protected $url;
	/**
	 * 
	 */
	public function __construct($url)
	{	
		global $tpl;
		
		parent::__construct('');
		$this->url = $url;
		$tpl->addJavaScript('Services/Mail/js/ilMailComposeFunctions.js');
	}

	/**
	 * @param string $placeholder
	 * @param string $title
	 */
	public function addPlaceholder($placeholder, $title)
	{
		$this->placeholders[$placeholder]['placeholder'] = $placeholder;
		$this->placeholders[$placeholder]['title'] = $title;
	}

	/**
	 * @param $a_tpl
	 */
	public function insert($a_tpl)
	{
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}

	/**
	 * @param bool $ajax
	 * @return string|void
	 */
	public function render($ajax = false)
	{
		global $lng;
		
		$subtpl = new ilTemplate("tpl.mail_manual_placeholders.html", true, true, "Services/Mail");
		$subtpl->setVariable('TXT_USE_PLACEHOLDERS', $lng->txt('mail_nacc_use_placeholder'));
		$subtpl->setVariable('TXT_PLACEHOLDERS_ADVISE', sprintf($lng->txt('placeholders_advise'), '<br />'));
		if(count($this->placeholders) > 0)
		{
			foreach($this->placeholders as $placeholder)
			{
				$subtpl->setCurrentBlock('man_placeholder');
				$subtpl->setVariable('MANUAL_PLACEHOLDER', $placeholder['placeholder']);
				$subtpl->setVariable('TXT_MANUAL_PLACEHOLDER', $placeholder['title']);
				$subtpl->parseCurrentBlock();
			}
		}
		if($ajax)
		{
			echo $subtpl->get();
			exit();
		}
		else
		{
			$subtpl->setVariable('URL', $this->url);
			return $subtpl->get();
		}

	}

	/**
	 * Set value by array
	 *
	 * @param	array	$a_values	value array
	 */
	function setValueByArray($a_values)
	{
		$this->setValue($a_values[$this->getPostVar()]);
	}
	/**
	 * Set Value.
	 *
	 * @param	string	$a_value	Value
	 */
	function setValue($a_value)
	{
		if($this->getMulti() && is_array($a_value))
		{
			$this->setMultiValues($a_value);
			$a_value = array_shift($a_value);
		}
		$this->value = $a_value;
	}
	
	function checkInput()
	{
		return true;
	}

}
	