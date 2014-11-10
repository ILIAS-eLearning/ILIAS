<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Button GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilTabsGUI.php 45109 2013-09-30 15:46:28Z akill $
 * @package ServicesUIComponent
 */
abstract class ilButton
{
	protected $type; // [int]
	protected $id; // [string]
	protected $caption; // [string]
	protected $caption_is_lng_id; // [bool]
	protected $primary; // [bool]
	protected $omit_prevent_double_submission; // [bool]
	protected $onclick; // [string]
	protected $acc_key; // [string]
	protected $disabled; // [bool]
	protected $css = array(); // [array]
	
	const TYPE_SUBMIT = 1;
	const TYPE_LINK = 2;
	
	/**
	 * Constructor
	 * 
	 * @param int $a_type
	 * @return self
	 */
	protected function __construct($a_type)
	{
		$this->setType($a_type);
	}
	
	/**
	 * Clone instance
	 */
	public function __clone() 
	{
		$this->setId(null);
	}
	
	/**
	 * Factory
	 * 
	 * @return self
	 */
	abstract public static function getInstance();
	
	
	//
	// properties
	//
	
	/**
	 * Set button type
	 * 
	 * @param int $a_value
	 */
	protected function setType($a_value)
	{
		$this->type = (int)$a_value;
	}
	
	/**
	 * Get button type
	 * 
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}
		
	/**
	 * Set id
	 * 
	 * @param string $a_value
	 */
	public function setId($a_value)
	{
		$this->id = $a_value;
	}
	
	/**
	 * Get id
	 * 
	 * @return string 
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Set caption
	 * 
	 * @param string $a_value
	 * @param bool $a_is_lng_id
	 */
	public function setCaption($a_value, $a_is_lng_id = true)
	{
		$this->caption = $a_value;
		$this->caption_is_lng_id = (bool)$a_is_lng_id;
	}
	
	/**
	 * Get caption
	 * 
	 * @param bool $a_translate
	 * @return string
	 */
	public function getCaption($a_translate = true)
	{
		global $lng;
		
		$caption = $this->caption;
		
		if($this->caption_is_lng_id &&
			(bool)$a_translate)
		{
			$caption = $lng->txt($caption);
		}
	
		return $caption;
	}

	/**
	 * Toggle primary status
	 * 
	 * @param bool $a_value
	 */
	public function setPrimary($a_value)
	{
		$this->primary = (bool)$a_value;
	}
	
	/**
	 * Get primary status
	 * 
	 * @return bool
	 */
	public function isPrimary()
	{
		return $this->primary;
	}
	
	/**
	 * Toggle double submission prevention status
	 * 
	 * @param bool $a_value
	 */
	public function setOmitPreventDoubleSubmission($a_value)
	{
		$this->omit_prevent_double_submission = (bool)$a_value;
	}
	
	/**
	 * Get double submission prevention status
	 * 
	 * @return bool
	 */
	public function getOmitPreventDoubleSubmission()
	{
		return $this->omit_prevent_double_submission;
	}
	
	/**
	 * Set onclick
	 * 
	 * @param string $a_value
	 */
	public function setOnClick($a_value)
	{
		$this->onclick = trim($a_value);
	}
	
	/**
	 * Get onclick
	 * 
	 * @return string
	 */
	public function getOnClick()
	{
		return $this->onclick;
	}
	
	/**
	 * Set access key
	 * 
	 * @param string $a_value
	 */
	public function setAccessKey($a_value)
	{
		$this->acc_key = trim($a_value);
	}
	
	/**
	 * Get access key
	 * 
	 * @return string 
	 */
	public function getAccessKey()
	{
		return $this->acc_key;
	}
	
	/**
	 * Toggle disabled status
	 * 
	 * @param bool $a_value
	 */
	public function setDisabled($a_value)
	{
		$this->disabled = (bool)$a_value;
	}
	
	/**
	 * Get disabled status
	 * 
	 * @return bool 
	 */
	public function isDisabled()
	{
		return $this->disabled;
	}
	
	/**
	 * Add CSS class
	 * 
	 * @param string $a_value
	 */
	public function addCSSClass($a_value)
	{
		$this->css[] = $a_value;
	}
	
	/**
	 * Get CSS class(es)
	 * 
	 * @return array
	 */
	public function getCSSClasses()
	{
		return $this->css;
	}		
	
	
	//
	// render
	//
	
	/**
	 * Gather all active CSS classes
	 * 
	 * @return string
	 */
	protected function gatherCssClasses()
	{
		$css = array_unique($this->getCSSClasses());
	
		if($this->isPrimary())
		{
			$css[] = "btn-primary";
		}
		if($this->getOmitPreventDoubleSubmission())
		{
			$css[] = "omitPreventDoubleSubmission";
		}
		
		return implode(" ", $css);
	}
	
	/**
	 * Render HTML node attributes
	 * 
	 * @return string
	 */
	protected function renderAttributesHelper(array $a_attr)
	{
		$res = array();
		
		foreach($a_attr as $id => $value)
		{
			if(trim($value))
			{
				$res[] = strtolower(trim($id)).'="'.$value.'"';
			}
		}
		
		if(sizeof($res))
		{
			return " ".implode(" ", $res);
		}
	}		
	
	/**
	 * Render current HTML attributes
	 * 
	 * @param array $a_additional_attr
	 * @return string
	 */	 
	protected function renderAttributes(array $a_additional_attr = null)
	{		
		$attr = array();
		$attr["id"] = $this->getId();
		$attr["class"] = $this->gatherCssClasses();
		$attr["onclick"] = $this->getOnClick();				
		
		if($this->getAccessKey())
		{
			include_once("./Services/Accessibility/classes/class.ilAccessKey.php");
			$attr["accesskey"] = ilAccessKey::getKey($this->getAccessKey());
		}
		
		if($this->isDisabled())
		{
			$attr["disabled"] = "disabled";
		}
		
		if(sizeof($a_additional_attr))
		{
			$attr = array_merge($attr, $a_additional_attr);
		}
		
		return $this->renderAttributesHelper($attr);
	}
	
	/**
	 * Prepare render	
	 */
	protected function prepareRender()
	{		
		$this->addCSSClass("btn");
		$this->addCSSClass("btn-default");
	}
	 
	/**
	 * Render HTML
	 * 
	 * @return string
	 */
	abstract public function render();
}