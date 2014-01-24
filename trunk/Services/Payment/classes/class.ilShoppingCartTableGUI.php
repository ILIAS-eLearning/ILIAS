<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
* Class ilShoppingCartTableGUI
*

* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id:$
* 
* @ingroup ServicesPayment
*  
*/
class ilShoppingCartTableGUI extends ilTable2GUI
{
	private $total_data = array();

	/**
	 * Constructor
	 *
	 * @access	public
	 *
	 */
	public function __construct($a_parent_obj, $a_parent_cmd = "")
	{

	 	global $lng,$ilCtrl;

	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;

	 	parent::__construct($a_parent_obj, $a_parent_cmd);


		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
	}

	/**
	 * Fill row
	 *
	 * @access public
	 * @param
	 *
	 */
	public function fillRow($a_set)
	{
		foreach ($a_set as $key => $value)
		{
			$this->tpl->setVariable("VAL_".strtoupper($key), $value);
		}
	}
/**
 * set total data
 *
 * @access public
 *
 *
 *
 */

	public function setTotalData($a_key , $a_value)
	{
		$this->total_data[$a_key] = $a_value;
	}


	public function getTotalData()
	{
		return $this->total_data;
	}

	/**
	* Get HTML: only for shoppingcarts
	 * does the same as getHTML of table2gui
	 * but additional
	*/
	public function getCartHTML()
	{

		global $lng, $ilCtrl, $ilUser;

		$this->prepareOutput();

		if (is_object($ilCtrl) && $this->getId() == "")
		{
			$ilCtrl->saveParameter($this->getParentObject(), $this->getNavParameter());
		}

		if(!$this->enabled['content'])
		{
			return $this->render();
		}

		if (!$this->getExternalSegmentation())
		{
			$this->setMaxCount(count($this->row_data));
		}

		$this->determineOffsetAndOrder();

		$this->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// sort
		$data = $this->getData();
		if (!$this->getExternalSorting())
		{
			$data = ilUtil::sortArray($data, $this->getOrderField(),
				$this->getOrderDirection(), $this->numericOrdering($this->getOrderField()));
		}

		// slice
		if (!$this->getExternalSegmentation())
		{
			$data = array_slice($data, $this->getOffset(), $this->getLimit());
		}

		// fill rows
		if(count($data) > 0)
		{
			$this->tpl->addBlockFile("TBL_CONTENT", "tbl_content", $this->row_template,
				$this->row_template_dir);

			foreach($data as $set)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$this->css_row = ($this->css_row != "tblrow1")
					? "tblrow1"
					: "tblrow2";
				$this->tpl->setVariable("CSS_ROW", $this->css_row);

				$this->fillRow($set);
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->parseCurrentBlock();
			}
			// Rows for Total Amounts
			$this->css_row_2 = ($this->css_row == "tblrow1")
					? "tblrow2"
					: "tblrow1";

			$this->tpl->setCurrentBlock('totals_row');
	
			foreach($this->total_data as $key => $value)
			{
				$this->tpl->setVariable('CSS_ROW_2', $this->css_row_2);
				$this->tpl->setVariable($key,  $value);
			}
			$this->tpl->parseCurrentBlock();
			// */
		}
		else
		{
			// add standard no items text (please tell me, if it messes something up, alex, 29.8.2008)
			$no_items_text = (trim($this->getNoEntriesText()) != '')
				? $this->getNoEntriesText()
				: $lng->txt("no_items");

			$this->css_row = ($this->css_row != "tblrow1")
					? "tblrow1"
					: "tblrow2";

			$this->tpl->setCurrentBlock("tbl_no_entries");
			$this->tpl->setVariable('TBL_NO_ENTRY_CSS_ROW', $this->css_row);
			$this->tpl->setVariable('TBL_NO_ENTRY_COLUMN_COUNT', $this->column_count);
			$this->tpl->setVariable('TBL_NO_ENTRY_TEXT', trim($no_items_text));
			$this->tpl->parseCurrentBlock();
		}

		// set form action
		if ($this->form_action != "")
		{
			$hash = "";
			if (is_object($ilUser) && $ilUser->prefs["screen_reader_optimization"])
			{
				$hash = "#".$this->getTopAnchor();
			}

			$this->tpl->setCurrentBlock("tbl_form_header");
			$this->tpl->setVariable("FORMACTION", $this->getFormAction().$hash);
			$this->tpl->setVariable("FORMNAME", $this->getFormName());
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("tbl_form_footer");
		}

		// fill Totals Row
		$this->fillFooter();

		$this->fillHiddenRow();

		$this->fillActionRow();

		$this->storeNavParameter();

		return $this->render();
	}
}
?>