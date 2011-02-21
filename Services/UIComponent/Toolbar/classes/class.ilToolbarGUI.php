<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Toolbar. The toolbar currently only supports a list of buttons as links.
*
* A default toolbar object is available in the $ilToolbar global object.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesUIComponent
*/
class ilToolbarGUI
{
	var $items = array();
	var $open_form_tag = true;
	var $close_form_tag = true;
	var $form_target = "";

	function __construct()
	{
	
	}

	/**
	* Set form action (if form action is set, toolbar is wrapped into form tags
	*
	* @param	string	form action
	*/
	function setFormAction($a_val, $a_multipart = false, $a_target = "")
	{
		$this->form_action = $a_val;
		$this->multipart = $a_multipart;
		$this->form_target = $a_target;
	}
	
	/**
	* Get form action
	*
	* @return	string	form action
	*/
	function getFormAction()
	{
		return $this->form_action;
	}

	/**
	* Set leading image
	*/
	function setLeadingImage($a_img, $a_alt)
	{
		$this->lead_img = array("img" => $a_img, "alt" => $a_alt);
	}
	
	/**
	* Add button to toolbar
	*
	* @param	string		text
	* @param	string		link href / submit command
	* @param	string		frame target
	* @param	string		access key
	*/
	public function addButton($a_txt, $a_cmd, $a_target = "", $a_acc_key = "", $a_additional_attrs = '')
	{
		$this->items[] = array("type" => "button", "txt" => $a_txt, "cmd" => $a_cmd,
			"target" => $a_target, "acc_key" => $a_acc_key, 'add_attrs' => $a_additional_attrs);
	}

	/**
	* Add form button to toolbar
	*
	* @param	string		text
	* @param	string		link href / submit command
	* @param	string		access key
	*/
	function addFormButton($a_txt, $a_cmd, $a_acc_key = "")
	{
		$this->items[] = array("type" => "fbutton", "txt" => $a_txt, "cmd" => $a_cmd,
			"acc_key" => $a_acc_key);
	}
	
	/**
	* Add input item
	*/
	function addInputItem(ilToolbarItem $a_item, $a_output_label = false)
	{
		$this->items[] = array("type" => "input", "input" => $a_item, "label" => $a_output_label);
	}
	
	/**
	* Add separator
	*/
	function addSeparator()
	{
		$this->items[] = array("type" => "separator");
	}
	
	/**
	* Add spacer
	*/
	function addSpacer()
	{
		$this->items[] = array("type" => "spacer");
	}

	/**
	 * Set open form tag
	 *
	 * @param	boolean	open form tag
	 */
	function setOpenFormTag($a_val)
	{
		$this->open_form_tag = $a_val;
	}

	/**
	 * Get open form tag
	 *
	 * @return	boolean	open form tag
	 */
	function getOpenFormTag()
	{
		return $this->open_form_tag;
	}

	/**
	 * Set close form tag
	 *
	 * @param	boolean	close form tag
	 */
	function setCloseFormTag($a_val)
	{
		$this->close_form_tag = $a_val;
	}

	/**
	 * Get close form tag
	 *
	 * @return	boolean	close form tag
	 */
	function getCloseFormTag()
	{
		return $this->close_form_tag;
	}

	/**
	* Get toolbar html
	*/
	function getHTML()
	{
		global $lng;
		
		$tpl = new ilTemplate("tpl.toolbar.html", true, true, "Services/UIComponent/Toolbar");
		if (count($this->items) > 0)
		{
			foreach($this->items as $item)
			{
				switch ($item["type"])
				{
					case "button":						
						$tpl->setCurrentBlock("button");
						$tpl->setVariable("BTN_TXT", $item["txt"]);
						$tpl->setVariable("BTN_LINK", $item["cmd"]);
						if ($item["target"] != "")
						{
							$tpl->setVariable("BTN_TARGET", 'target="'.$item["target"].'"');
						}
						if ($item["acc_key"] != "")
						{
							include_once("./Services/Accessibility/classes/class.ilAccessKeyGUI.php");
							$tpl->setVariable("BTN_ACC_KEY",
								ilAccessKeyGUI::getAttribute($item["acc_key"]));
						}
						if(($item['add_attrs']))
						{
							$tpl->setVariable('BTN_ADD_ARG',$item['add_attrs']);
						}
						$tpl->parseCurrentBlock();
						$tpl->touchBlock("item");
						break;
					
					case "fbutton":
						$tpl->setCurrentBlock("form_button");
						$tpl->setVariable("SUB_TXT", $item["txt"]);
						$tpl->setVariable("SUB_CMD", $item["cmd"]);
						$tpl->parseCurrentBlock();
						$tpl->touchBlock("item");
						break;
						
					case "input":
						if ($item["label"])
						{
							$tpl->setCurrentBlock("input_label");
							$tpl->setVariable("TXT_INPUT", $item["input"]->getTitle());
							$tpl->parseCurrentBlock();
						}
						$tpl->setCurrentBlock("input");
						$tpl->setVariable("INPUT_HTML", $item["input"]->getToolbarHTML());
						$tpl->parseCurrentBlock();
						$tpl->touchBlock("item");
						break;
						
					case "separator":
						$tpl->touchBlock("separator");
						$tpl->touchBlock("item");
						break;

					case "spacer":
						$tpl->touchBlock("spacer");
						$tpl->touchBlock("item");
						break;
				}
			}
			
			$tpl->setVariable("TXT_FUNCTIONS", $lng->txt("functions"));
			if ($this->lead_img["img"] != "")
			{
				$tpl->setCurrentBlock("lead_image");				
				$tpl->setVariable("IMG_SRC", $this->lead_img["img"]);
				$tpl->setVariable("IMG_ALT", $this->lead_img["alt"]);
				$tpl->parseCurrentBlock();
			}
			
			// form?
			if ($this->getFormAction() != "")
			{
				if ($this->getOpenFormTag())
				{
					$tpl->setCurrentBlock("form_open");
					$tpl->setVariable("FORMACTION", $this->getFormAction());
					if ($this->multipart)
					{
						$tpl->setVariable("ENC_TYPE", 'enctype="multipart/form-data"');
					}
					if ($this->form_target != "")
					{
						$tpl->setVariable("TARGET", ' target="'.$this->form_target.'" ');
					}
					$tpl->parseCurrentBlock();
				}
				if ($this->getCloseFormTag())
				{
					$tpl->touchBlock("form_close");
				}
			}
			
			return $tpl->get();
		}
		return "";
	}
}
