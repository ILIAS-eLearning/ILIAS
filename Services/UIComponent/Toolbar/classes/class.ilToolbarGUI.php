<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	var $form_name = "";

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
	 * Set hidden
	 *
	 * @param boolean $a_val hidden	
	 */
	function setHidden($a_val)
	{
		$this->hidden = $a_val;
	}
	
	/**
	 * Get hidden
	 *
	 * @return boolean hidden
	 */
	function getHidden()
	{
		return $this->hidden;
	}
	
	/**
	 * Set id
	 *
	 * @param string $a_val id	
	 */
	function setId($a_val)
	{
		$this->id = $a_val;
	}
	
	/**
	 * Get id
	 *
	 * @return string id
	 */
	function getId()
	{
		return $this->id;
	}
	
	/**
	* Add button to toolbar
	* 
	* @deprecated use addButtonInstance() instead! 
	*
	* @param	string		text
	* @param	string		link href / submit command
	* @param	string		frame target
	* @param	string		access key
	*/
	public function addButton($a_txt, $a_cmd, $a_target = "", $a_acc_key = "", $a_additional_attrs = '',
		$a_id = "", $a_class = 'submit')
	{
		$this->items[] = array("type" => "button", "txt" => $a_txt, "cmd" => $a_cmd,
			"target" => $a_target, "acc_key" => $a_acc_key, 'add_attrs' => $a_additional_attrs,
			"id" => $a_id, "class" => $a_class);
	}

	/**
	* Add form button to toolbar
	* 
	* deprecated use addButtonInstance() instead! 
	*
	* @param	string		text
	* @param	string		link href / submit command
	* @param	string		access key
	* @param	bool		primary action
	* @param	string		css class
	*/
	function addFormButton($a_txt, $a_cmd, $a_acc_key = "", $a_primary = false, $a_class = false)
	{
		$this->items[] = array("type" => "fbutton", "txt" => $a_txt, "cmd" => $a_cmd,
			"acc_key" => $a_acc_key, "primary" => $a_primary, "class" => $a_class);
	}
	
	/**
	* Add input item
	*/
	public function addInputItem(ilToolbarItem $a_item, $a_output_label = false)
	{
		$this->items[] = array("type" => "input", "input" => $a_item, "label" => $a_output_label);
	}

	/**
	 * Add button instance
	 * 
	 * @param ilButton $a_button
	 */
	public function addButtonInstance(ilButton $a_button)
	{
		$this->items[] = array("type" => "button_obj", "instance" => $a_button); 
	}

	// bs-patch start
	/**
	 * Add input item
	 */
	public function addDropDown($a_txt, $a_dd_html)
	{
		$this->items[] = array("type" => "dropdown", "txt" => $a_txt, "dd_html" => $a_dd_html);
	}
	// bs-patch end

	/**
	* Add separator
	*/
	function addSeparator()
	{
		$this->items[] = array("type" => "separator");
	}

	/**
	* Add text
	*/
	function addText($a_text)
	{
		$this->items[] = array("type" => "text", "text" => $a_text);
	}

	/**
	* Add spacer
	*/
	function addSpacer($a_width = null)
	{
		$this->items[] = array("type" => "spacer", "width" => $a_width);
	}
 

	/**
	 * Add link
	 *
	 * @param  string $a_caption
	 * @param string $a_url
         * @param boolean $a_disabled
	 */
	function addLink($a_caption, $a_url, $a_disabled = false)
	{
		$this->items[] = array("type" => "link", "txt" => $a_caption, "cmd" => $a_url, "disabled" => $a_disabled);
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
	 * Set form name
	 *
	 * @param	string form name
	 */
	function setFormName($a_val)
	{
		$this->form_name = $a_val;
	}

	/**
	 * Get form name
	 *
	 * @return	string form name
	 */
	function getFormName()
	{
		return $this->form_name;
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
						if ($item["id"] != "")
						{
							$tpl->setVariable("BID", 'id="'.$item["id"].'"');
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
						$tpl->setVariable('BTN_CLASS',$item['class']);
						$tpl->parseCurrentBlock();
						$tpl->touchBlock("item");
						break;
					
					case "fbutton":
						$tpl->setCurrentBlock("form_button");
						$tpl->setVariable("SUB_TXT", $item["txt"]);
						$tpl->setVariable("SUB_CMD", $item["cmd"]);
						if($item["primary"])
						{
							$tpl->setVariable("SUB_CLASS", " emphsubmit");
						}
						else if($item["class"])
						{
							$tpl->setVariable("SUB_CLASS", " ".$item["class"]);
						}
						$tpl->parseCurrentBlock();
						$tpl->touchBlock("item");
						break;
						
					case "button_obj":
						$tpl->setCurrentBlock("button_instance");
						$tpl->setVariable("BUTTON_OBJ", $item["instance"]->render());
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

					// bs-patch start
					case "dropdown":
						$tpl->setCurrentBlock("dropdown");
						$tpl->setVariable("TXT_DROPDOWN", $item["txt"]);
						$tpl->setVariable("DROP_DOWN", $item["dd_html"]);
						$tpl->parseCurrentBlock();
						$tpl->touchBlock("item");
						break;
					// bs-patch end


					case "separator":
						$tpl->touchBlock("separator");
						$tpl->touchBlock("item");
						break;

					case "text":
						$tpl->setCurrentBlock("text");
						$tpl->setVariable("VAL_TEXT", $item["text"]);
						$tpl->touchBlock("item");
						break;

					case "spacer":
						$tpl->touchBlock("spacer");
						if(!$item["width"])
						{
							$item["width"] = 2;
						}
						$tpl->setVariable("SPACER_WIDTH", $item["width"]);
						$tpl->touchBlock("item");
						break;

					case "link":
                                                if ($item["disabled"] == false) {
                                                    $tpl->setCurrentBlock("link");
                                                    $tpl->setVariable("LINK_TXT", $item["txt"]);
                                                    $tpl->setVariable("LINK_URL", $item["cmd"]);
                                                    $tpl->parseCurrentBlock();
                                                    $tpl->touchBlock("item");
                                                    break;
                                                }
                                                else {
                                                    $tpl->setCurrentBlock("link_disabled");
                                                    $tpl->setVariable("LINK_DISABLED_TXT", $item["txt"]);
                                                    //$tpl->setVariable("LINK_URL", $item["cmd"]);
                                                    $tpl->parseCurrentBlock();
                                                    $tpl->touchBlock("item");
                                                    break;
                                                }
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
					if ($this->form_name != "")
					{
						$tpl->setVariable("FORMNAME", 'name="'.$this->getFormName().'"');
					}

					$tpl->parseCurrentBlock();
				}
				if ($this->getCloseFormTag())
				{
					$tpl->touchBlock("form_close");
				}
			}
			
			// id
			if ($this->getId() != "")
			{
				$tpl->setVariable("ID", ' id="'.$this->getId().'" ');
			}
			
			// hidden style
			if ($this->getHidden())
			{
				$tpl->setVariable("HIDDEN_CLASS", 'ilNoDisplay');
			}
			
			return $tpl->get();
		}
		return "";
	}
}
