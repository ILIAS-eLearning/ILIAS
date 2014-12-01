<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* User interface class for a checkbox list overlay
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id:$
*/
class ilCheckboxListOverlayGUI
{
	private $items = array();
	
	/**
	* Constructor.
	*	
	*/
	public function __construct($a_id = "")
	{
		$this->setId($a_id);
	}

	/**
	 * Set id
	 *
	 * @param	string	id
	 */
	function setId($a_val)
	{
		$this->id = $a_val;
	}
	
	/**
	 * Get id
	 *
	 * @return	string	id
	 */
	function getId()
	{
		return $this->id;
	}
	

	/**
	 * Set link title
	 *
	 * @param	string	link title
	 */
	function setLinkTitle($a_val)
	{
		$this->link_title = $a_val;
	}
	
	/**
	 * Get link title
	 *
	 * @return	string	link title
	 */
	function getLinkTitle()
	{
		return $this->link_title;
	}
	
	/**
	 * Set items
	 *
	 * @param	array	items
	 */
	function setItems($a_val)
	{
		$this->items = $a_val;
	}
	
	/**
	 * Get items
	 *
	 * @return	array	items
	 */
	function getItems()
	{
		return $this->items;
	}
	
	/**
	* Set Selection Header Class.
	*
	* @param	string	$a_selectionheaderclass	Selection Header Class
	*/
	function setSelectionHeaderClass($a_selectionheaderclass)
	{
		$this->selectionheaderclass = $a_selectionheaderclass;
	}

	/**
	* Get Selection Header Class.
	*
	* @return	string	Selection Header Class
	*/
	function getSelectionHeaderClass()
	{
		return $this->selectionheaderclass;
	}

	/**
	 * Set form command
	 *
	 * @param	string	form command
	 */
	function setFormCmd($a_val)
	{
		$this->form_cmd = $a_val;
	}
	
	/**
	 * Get form command
	 *
	 * @return	string	form command
	 */
	function getFormCmd()
	{
		return $this->form_cmd;
	}
	
	/**
	 * Set field var
	 *
	 * @param	string	field var
	 */
	function setFieldVar($a_val)
	{
		$this->field_var = $a_val;
	}
	
	/**
	 * Get field var
	 *
	 * @return	string	field var
	 */
	function getFieldVar()
	{
		return $this->field_var;
	}
	
	/**
	 * Set hidden var (used to indicated that checkbox array has been sent in a form)
	 *
	 * @param	string	hidden var
	 */
	function setHiddenVar($a_val)
	{
		$this->hidden_var = $a_val;
	}
	
	/**
	 * Get hidden var
	 *
	 * @return	string	hidden var
	 */
	function getHiddenVar()
	{
		return $this->hidden_var;
	}
	/**
	* Get selection list HTML
	*/
	public function getHTML()
	{
		global $lng;
		
		$items = $this->getItems();

		$tpl = new ilTemplate("tpl.checkbox_list_overlay.html", true, true,
			"Services/UIComponent/CheckboxListOverlay", "DEFAULT", false, true);
				
		$tpl->setCurrentBlock("top_img");
		
		// do not repeat title (accessibility) -> empty alt
		$tpl->setVariable("TXT_SEL_TOP", $this->getLinkTitle());
		$tpl->setVariable("ALT_SEL_TOP", "");
		
		$tpl->parseCurrentBlock();
		
		reset($items);
		$cnt = 0;
		foreach ($items as $k => $v)
		{			
			$tpl->setCurrentBlock("list_entry");
			$tpl->setVariable("VAR", $this->getFieldVar());
			$tpl->setVariable("VAL_ENTRY", $k);
			$tpl->setVariable("TXT_ENTRY", $v["txt"]);
			$tpl->setVariable("IDX_ENTRY", ++$cnt); 
			if ($v["selected"])
			{
				$tpl->setVariable("CHECKED", "checked='checked'");
			}
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setVariable("ID", $this->getId());
		$tpl->setVariable("HIDDEN_VAR", $this->getHiddenVar());
		$tpl->setVariable("CMD_SUBMIT", $this->getFormCmd());
		$tpl->setVariable("VAL_SUBMIT", $lng->txt("refresh"));				
		return $tpl->get();
	}
}
?>
