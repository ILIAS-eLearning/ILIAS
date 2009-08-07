<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents a property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilFormPropertyGUI
{
	protected $type;
	protected $title;
	protected $postvar;
	protected $info;
	protected $alert;
	protected $required = false;
	protected $parentgui;
	protected $parentform;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		$this->setTitle($a_title);
		$this->setPostVar($a_postvar);
		$this->setDisabled(false);
	}

	/**
	* Execute command.
	*/
	function &executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		
		return $this->$cmd();
	}

	/**
	* Set Type.
	*
	* @param	string	$a_type	Type
	*/
	protected function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	* Get Type.
	*
	* @return	string	Type
	*/
	function getType()
	{
		return $this->type;
	}

	/**
	* Set Title.
	*
	* @param	string	$a_title	Title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get Title.
	*
	* @return	string	Title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* Set Post Variable.
	*
	* @param	string	$a_postvar	Post Variable
	*/
	function setPostVar($a_postvar)
	{
		$this->postvar = $a_postvar;
	}

	/**
	* Get Post Variable.
	*
	* @return	string	Post Variable
	*/
	function getPostVar()
	{
		return $this->postvar;
	}

	/**
	* Get Post Variable.
	*
	* @return	string	Post Variable
	*/
	function getFieldId()
	{
		$id = str_replace("[", "__", $this->getPostVar());
		$id = str_replace("]", "__", $id);
		
		return $id;
	}

	/**
	* Set Information Text.
	*
	* @param	string	$a_info	Information Text
	*/
	function setInfo($a_info)
	{
		$this->info = $a_info;
	}

	/**
	* Get Information Text.
	*
	* @return	string	Information Text
	*/
	function getInfo()
	{
		return $this->info;
	}

	/**
	* Set Alert Text.
	*
	* @param	string	$a_alert	Alert Text
	*/
	function setAlert($a_alert)
	{
		$this->alert = $a_alert;
	}

	/**
	* Get Alert Text.
	*
	* @return	string	Alert Text
	*/
	function getAlert()
	{
		return $this->alert;
	}

	/**
	* Set Required.
	*
	* @param	boolean	$a_required	Required
	*/
	function setRequired($a_required)
	{
		$this->required = $a_required;
	}

	/**
	* Get Required.
	*
	* @return	boolean	Required
	*/
	function getRequired()
	{
		return $this->required;
	}
	
	/**
	* Set Disabled.
	*
	* @param	boolean	$a_disabled	Disabled
	*/
	function setDisabled($a_disabled)
	{
		$this->disabled = $a_disabled;
	}

	/**
	* Get Disabled.
	*
	* @return	boolean	Disabled
	*/
	function getDisabled()
	{
		return $this->disabled;
	}
	
	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		return false;		// please overwrite
	}

	/**
	* Set Parent Form.
	*
	* @param	object	$a_parentform	Parent Form
	*/
	function setParentForm($a_parentform)
	{
		$this->setParent($a_parentform);
	}

	/**
	* Get Parent Form.
	*
	* @return	object	Parent Form
	*/
	function getParentForm()
	{
		return $this->getParent();
	}

	/**
	* Set Parent GUI object.
	*
	* @param	object	parent gui object
	*/
	function setParent($a_val)
	{
		$this->parent_gui = $a_val;
	}
	
	/**
	* Get  Parent GUI object.
	*
	* @return	object	parent gui object
	*/
	function getParent()
	{
		return $this->parent_gui;
	}

	/**
	* Get sub form html
	*
	*/
	public function getSubForm()
	{
		return "";
	}

	/**
	* Sub form hidden on init?
	*
	*/
	public function hideSubForm()
	{
		return false;
	}

	/**
	* Set hidden title (for screenreaders)
	*
	* @param	string	hidden title
	*/
	function setHiddenTitle($a_val)
	{
		$this->hidden_title = $a_val;
	}
	
	/**
	* Get hidden title
	*
	* @return	string	hidden title
	*/
	function getHiddenTitle()
	{
		return $this->hidden_title;
	}
	
	/**
	* Get item by post var
	*
	* @return	mixed	false or item object
	*/
	function getItemByPostVar($a_post_var)
	{
		if ($this->getPostVar() == $a_post_var)
		{
			return $this;
		}
		
		return false;
	}
	
	/**
	* serialize data
	*/
	function serializeData()
	{
		return serialize($this->getValue());
	}
	
	/**
	* unserialize data
	*/
	function unserializeData($a_data)
	{
		$data = unserialize($a_data);

		if ($data)
		{
			$this->setValue($data);
		}
		else
		{
			$this->setValue(false);
		}
	}
	
	/**
	* Write to session
	*/
	function writeToSession()
	{
		$parent = $this->getParent();
		if (!is_object($parent))
		{
			die("You must set parent for ".get_class($this)." to use serialize feature.");
		}
		$_SESSION["form_".$parent->getId()][$this->getFieldId()] =
			$this->serializeData();
	}

	/**
	* Read from session
	*/
	function readFromSession()
	{
		$parent = $this->getParent();
		if (!is_object($parent))
		{
			die("You must set parent for ".get_class($this)." to use serialize feature.");
		}
		$this->unserializeData($_SESSION["form_".$parent->getId()][$this->getFieldId()]);
	}
	
	/**
	 * Get hidden tag (used for disabled properties)
	 */
	function getHiddenTag($a_post_var, $a_value)
	{
		return '<input type="hidden" name="'.$a_post_var.'" value="'.ilUtil::prepareFormOutput($a_value).'" />';
	}
	

}
?>