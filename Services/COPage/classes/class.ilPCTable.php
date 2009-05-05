<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCTable
*
* Table content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCTable extends ilPageContent
{
	var $dom;
	var $tab_node;


	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("tab");
	}

	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->tab_node =& $a_node->first_child();		// this is the Table node
	}

	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->tab_node =& $this->dom->create_element("Table");
		$this->tab_node =& $this->node->append_child($this->tab_node);
		$this->tab_node->set_attribute("Language", "");
	}

	function &addRow () {
		$new_tr =& $this->dom->create_element("TableRow");
		$new_tr = &$this->tab_node->append_child($new_tr);
		return $new_tr;
	}

	function &addCell (&$aRow, $a_data = "", $a_lang = "")
	{
		$new_td =& $this->dom->create_element("TableData");
		$new_td =& $aRow->append_child($new_td);
		
		// insert data if given
		if ($a_data != "")
		{
			$new_pg =& $this->createPageContentNode(false);
			$new_par =& $this->dom->create_element("Paragraph");
			$new_par =& $new_pg->append_child($new_par);
			$new_par->set_attribute("Language", $a_lang);
			$new_par->set_attribute("Characteristic", "TableContent");
			$new_par->set_content($a_data);
			$new_td->append_child ($new_pg);
		}
		
		return $new_td;
	}

	/**
	* add rows to table
	*/
	function addRows($a_nr_rows, $a_nr_cols)
	{
		for ($i=1; $i<=$a_nr_rows; $i++)
		{
			$aRow = $this->addRow();
			for ($j=1; $j<=$a_nr_cols; $j++)
			{
				$this->addCell($aRow);
			}
		}
	}
	
	/**
	* import from table
	*/
	function importSpreadsheet($a_lang, $a_data)
	{
		str_replace($a_data, "\r", "\n");
		str_replace($a_data, "\n\n", "\n");
		$target_rows = array();
		$rows = explode("\n", $a_data);

		// get maximum of cols in a row and
		// put data in target_row arrays
		foreach($rows as $row)
		{
			$cells = explode("\t", $row);
			$max_cols = ($max_cols > count($cells))
				? $max_cols
				: count($cells);
			$target_rows[] = $cells;
		}

		// iterate target row arrays and insert data
		foreach($target_rows as $row)
		{
			$aRow = $this->addRow();
			for ($j=0; $j<$max_cols; $j++)
			{
				// mask html
				$data = str_replace("&","&amp;", $row[$j]);
				$data = str_replace("<","&lt;", $data);
				$data = str_replace(">","&gt;", $data);

				$this->addCell($aRow, $data, $a_lang);
			}
		}
	}

	/**
	* get table language
	*/
	function getLanguage()
	{
		return $this->getTableAttribute("Language");
	}

	/**
	* set table language
	*
	* @param	string		$a_lang		language code
	*/
	function setLanguage($a_lang)
	{
		if($a_lang != "")
		{
			$this->setTableAttribute("Language", $a_lang);
		}
	}

	/**
	* get table width
	*/
	function getWidth()
	{
		return $this->getTableAttribute("Width");
	}

	/**
	* set table width
	*
	* @param	string		$a_width		table width
	*/
	function setWidth($a_width)
	{
		$this->setTableAttribute("Width", $a_width);
	}

	/**
	* get table border width
	*/
	function getBorder()
	{
		return $this->getTableAttribute("Border");
	}

	/**
	* set table border
	*
	* @param	string		$a_border		table border
	*/
	function setBorder($a_border)
	{
		$this->setTableAttribute("Border", $a_border);
	}

	/**
	* get table cell spacing
	*/
	function getCellSpacing()
	{
		return $this->getTableAttribute("CellSpacing");
	}

	/**
	* set table cell spacing
	*
	* @param	string		$a_spacing		table cell spacing
	*/
	function setCellSpacing($a_spacing)
	{
		$this->setTableAttribute("CellSpacing", $a_spacing);
	}

	/**
	* get table cell padding
	*/
	function getCellPadding()
	{
		return $this->getTableAttribute("CellPadding");
	}

	/**
	* set table cell padding
	*
	* @param	string		$a_padding		table cell padding
	*/
	function setCellPadding($a_padding)
	{
		$this->setTableAttribute("CellPadding", $a_padding);
	}

	/**
	* set horizontal align
	*/
	function setHorizontalAlign($a_halign)
	{
		$this->tab_node->set_attribute("HorizontalAlign", $a_halign);
	}

	/**
	* get table cell padding
	*/
	function getHorizontalAlign()
	{
		return $this->getTableAttribute("HorizontalAlign");
	}

	/**
	* set width of table data cell
	*/
	function setTDWidth($a_hier_id, $a_width, $a_pc_id = "")
	{
		$xpc = xpath_new_context($this->dom);

		if ($a_pc_id == "")
		{
			$path = "//TableData[@HierId = '".$a_hier_id."']";
		}
		else
		{
			$path = "//TableData[@PCID = '".$a_pc_id."']";
		}
		$res =& xpath_eval($xpc, $path);

		if (count($res->nodeset) == 1)
		{
			if($a_width != "")
			{
				$res->nodeset[0]->set_attribute("Width", $a_width);
			}
			else
			{
				if ($res->nodeset[0]->has_attribute("Width"))
				{
					$res->nodeset[0]->remove_attribute("Width");
				}
			}
		}
	}
	
	/**
	* Set TDSpans
	*/
	function setTDSpans($a_colspans, $a_rowspans)
	{
		$y = 0;
		$rows = $this->tab_node->child_nodes();
		foreach($rows as $row)
		{
			if ($row->node_name() == "TableRow")
			{
				$x = 0;
				$cells = $row->child_nodes();
				foreach($cells as $cell)
				{
					if ($cell->node_name() == "TableData")
					{
						$ckey = $cell->get_attribute("HierId").":".$cell->get_attribute("PCID");
						if((int) $a_colspans[$ckey] > 1)
						{
							$cell->set_attribute("ColSpan", (int) $a_colspans[$ckey]);
						}
						else
						{
							if ($cell->has_attribute("ColSpan"))
							{
								$cell->remove_attribute("ColSpan");
							}
						}
						if((int) $a_rowspans[$ckey] > 1)
						{
							$cell->set_attribute("RowSpan", (int) $a_rowspans[$ckey]);
						}
						else
						{
							if ($cell->has_attribute("RowSpan"))
							{
								$cell->remove_attribute("RowSpan");
							}
						}
					}
					$x++;
				}
				$y++;
			}
		}
		$this->fixHideAndSpans();
	}
	
	/**
	* Fix Hide and Spans. Reduces col and rowspans that are to high.
	* Sets Hide attribute for all cells that are hidden due to other span
	* attributes. Sets hidden cells to empty.
	*/
	function fixHideAndSpans()
	{
		
		// first: get max x and y
		$max_x = $max_y = 0;
		$y = 0;
		$rows = $this->tab_node->child_nodes();

		foreach($rows as $row)
		{
			if ($row->node_name() == "TableRow")
			{
				$x = 0;
				$cells = $row->child_nodes();
				foreach($cells as $cell)
				{
					if ($cell->node_name() == "TableData")
					{
						$max_x = max ($max_x, $x);
						$max_y = max ($max_y, $y);
					}
					$x++;
				}
				$y++;
			}
		}

		// second: fix hidden/colspans for all cells
		$y = 0;
		$rows = $this->tab_node->child_nodes();
		foreach($rows as $row)
		{
			if ($row->node_name() == "TableRow")
			{
				$x = 0;
				$cells = $row->child_nodes();
				foreach($cells as $cell)
				{
					if ($cell->node_name() == "TableData")
					{
						$cspan = max(1, (int) $cell->get_attribute("ColSpan"));
						$rspan = max(1, (int) $cell->get_attribute("RowSpan"));
						
						// if col or rowspan is to high: reduce it to the max
						if ($cspan > $max_x - $x + 1)
						{
							$cell->set_attribute("ColSpan", $max_x - $x + 1);
							$cspan = $max_x - $x + 1;
						}
						if ($rspan > $max_y - $y + 1)
						{
							$cell->set_attribute("RowSpan", $max_y - $y + 1);
							$rspan = $max_y - $y + 1;
						}
						
						// check hidden status
						if ($this->checkCellHidden($colspans, $rowspans, $x, $y))
						{
							// hidden: set hidden flag, remove col and rowspan
							$cell->set_attribute("Hidden", "Y");
							$cspan = 1;
							$rspan = 1;
							if ($cell->has_attribute("ColSpan"))
							{
								$cell->remove_attribute("ColSpan");
							}
							if ($cell->has_attribute("RowSpan"))
							{
								$cell->remove_attribute("RowSpan");
							}
							$this->makeEmptyCell($cell);
						}
						else
						{
							// not hidden: remove hidden flag if existing
							if ($cell->has_attribute("Hidden"))
							{
								$cell->remove_attribute("Hidden");
							}
						}
						
						$colspans[$x][$y] = $cspan;
						$rowspans[$x][$y] = $rspan;
					}
					$x++;
				}
				$y++;
			}
		}

	}

	
	/**
	* Make cell empty
	*/
	function makeEmptyCell($td_node)
	{
		// delete children of paragraph node
		$children = $td_node->child_nodes();
		for($i=0; $i<count($children); $i++)
		{
			$td_node->remove_child($children[$i]);
		}
	}

	/**
	* Check hidden status
	*/
	function checkCellHidden($colspans, $rowspans, $x, $y)
	{
		for ($i = 0; $i<=$x; $i++)
		{
			for ($j = 0; $j<=$y; $j++)
			{
				if ($i != $x || $j != $y)
				{
					if ((($i + $colspans[$i][$j] > $x) &&
						($j + $rowspans[$i][$j] > $y)))
					{
						return true;
					}
				}
			}
		}
		return false;
	}
	
	/**
	* Get all cell classes
	*
	* @return	array		array of cell style classes
	*/
	function getAllCellClasses()
	{
		$classes = array();
		$rows = $this->tab_node->child_nodes();
		foreach($rows as $row)
		{
			if ($row->node_name() == "TableRow")
			{
				$cells = $row->child_nodes();
				foreach($cells as $cell)
				{
					if ($cell->node_name() == "TableData")
					{
						$classes[$cell->get_attribute("HierId").":".$cell->get_attribute("PCID")]
							= $cell->get_attribute("Class");
					}
				}
			}
		}
		
		return $classes;
	}


	/**
	* Get all cell spans
	*
	* @return	array		array of cell style classes
	*/
	function getAllCellSpans()
	{
		$spans = array();
		$rows = $this->tab_node->child_nodes();
		$y = 0;
		$max_x = 0;
		$max_y = 0;
		foreach($rows as $row)
		{
			if ($row->node_name() == "TableRow")
			{
				$x = 0;
				$cells = $row->child_nodes();
				foreach($cells as $cell)
				{
					if ($cell->node_name() == "TableData")
					{
						$spans[$cell->get_attribute("HierId").":".$cell->get_attribute("PCID")]
							= array("x" => $x, "y" => $y, "colspan" => $cell->get_attribute("ColSpan"),
								"rowspan" => $cell->get_attribute("RowSpan"));
						$max_x = max($max_x, $x);
						$max_y = max($max_y, $y);
					}
					$x++;
				}
				$y++;
			}
		}
		foreach ($spans as $k => $v)
		{
			$spans[$k]["max_x"] = $max_x;
			$spans[$k]["max_y"] = $max_y;
		}
		
		return $spans;
	}

	/**
	* Get all cell widhts
	*
	* @return	array		array of cell style classes
	*/
	function getAllCellWidths()
	{
		$widths = array();
		$rows = $this->tab_node->child_nodes();
		foreach($rows as $row)
		{
			if ($row->node_name() == "TableRow")
			{
				$cells = $row->child_nodes();
				foreach($cells as $cell)
				{
					if ($cell->node_name() == "TableData")
					{
						$widths[$cell->get_attribute("HierId").":".$cell->get_attribute("PCID")]
							= $cell->get_attribute("Width");
					}
				}
			}
		}
		
		return $widths;
	}

	/**
	* set class of table data cell
	*/
	function setTDClass($a_hier_id, $a_class, $a_pc_id = "")
	{
		$xpc = xpath_new_context($this->dom);
		if ($a_pc_id == "")
		{
			$path = "//TableData[@HierId = '".$a_hier_id."']";
		}
		else
		{
			$path = "//TableData[@PCID = '".$a_pc_id."']";
		}
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			if($a_class != "")
			{
				$res->nodeset[0]->set_attribute("Class", $a_class);
			}
			else
			{
				if ($res->nodeset[0]->has_attribute("Class"))
				{
					$res->nodeset[0]->remove_attribute("Class");
				}
			}
		}
	}

	/**
	* get caption
	*/
	function getCaption()
	{
		$hier_id = $this->getHierId();
		if(!empty($hier_id))
		{
			$xpc = xpath_new_context($this->dom);
			$path = "//PageContent[@HierId = '".$hier_id."']/Table/Caption";
			$res =& xpath_eval($xpc, $path);

			if (count($res->nodeset) == 1)
			{
				return $res->nodeset[0]->get_content();
			}
		}
	}

	/**
	* get caption alignment (Top | Bottom)
	*/
	function getCaptionAlign()
	{
		$hier_id = $this->getHierId();
		if(!empty($hier_id))
		{
			$xpc = xpath_new_context($this->dom);
			$path = "//PageContent[@HierId = '".$hier_id."']/Table/Caption";
			$res =& xpath_eval($xpc, $path);
			if (count($res->nodeset) == 1)
			{
				return $res->nodeset[0]->get_attribute("Align");
			}
		}
	}

	/**
	* set table caption
	*/
	function setCaption($a_content, $a_align)
	{
		if ($a_content != "")
		{
			ilDOMUtil::setFirstOptionalElement($this->dom, $this->tab_node, "Caption",
			array("Summary", "TableRow"), $a_content,
			array("Align" => $a_align));
		}
		else
		{
			ilDOMUtil::deleteAllChildsByName($this->tab_node, array("Caption"));
		}
	}


	function importTableAttributes (&$node) {
		/*echo "importing table attributes";
		var_dump($tableNode);*/
		if ($node->has_attributes ())
		{
			foreach($node->attributes() as $n)
			{

				switch (strtolower($n->node_name ())) {
					case "border":
					$this->setBorder ($this->extractText($n));
					break;
					case "align":
					$this->setHorizontalAlign(ucfirst(strtolower($this->extractText($n))));
					break;
					case "cellspacing":
					$this->setCellSpacing($this->extractText($n));
					break;
					case "cellpadding":
					$this->setCellPadding($this->extractText($n));
					break;
					case "width":
					$this->setWidth($this->extractText($n));
					break;

				}

			}
		}
	}


	function importCellAttributes (&$node, &$par) {
		/*echo "importing table attributes";
		var_dump($tableNode);*/
		if ($node->has_attributes ())
		{
			foreach($node->attributes() as $n)
			{

				switch (strtolower($n->node_name ())) {
					case "class":
					$par->set_attribute("Class", $this->extractText($n));
					break;
					case "width":
					$par->set_attribute("Width", $this->extractText($n));
					break;
				}

			}
		}
	}


	function importRow ($lng, &$node) {
		/*echo "add Row";
		var_dump($node);*/

		$aRow = $this->addRow();

		if ($node->has_child_nodes())
		{
			foreach($node->child_nodes() as $n)
			{
				if ($n->node_type() == XML_ELEMENT_NODE &&
				strcasecmp($n->node_name (), "td") == 0)
				{
					$this->importCell ($lng, $n, $aRow);
				}
			}
		}
	}

	function importCell ($lng, &$cellNode, &$aRow) {
		/*echo "add Cell";
		var_dump($cellNode);*/
		$aCell = $this->addCell($aRow);
		$par = new ilPCParagraph($this->dom);
		$par->createAtNode($aCell);
		$par->setText($par->input2xml($this->extractText ($cellNode)));
		$par->setCharacteristic("TableContent");
		$par->setLanguage($lng);
		$this->importCellAttributes($cellNode, $aCell);
	}

	function extractText (&$node) {
		$owner_document = $node->owner_document ();
		$children = $node->child_nodes();
		$total_children = count($children);
		for ($i = 0; $i < $total_children; $i++){
			$cur_child_node = $children[$i];
			$output .= $owner_document->dump_node($cur_child_node);
		}
		return $output;
	}

	function importHtml ($lng, $htmlTable) {
		$dummy = ilUtil::stripSlashes($htmlTable, false);
		//echo htmlentities($dummy);
		$dom = @domxml_open_mem($dummy,DOMXML_LOAD_PARSING, $error);

		if ($dom)
		{
			$xpc = @xpath_new_context($dom);
			// extract first table object
			$path = "//table[1] | //Table[1]";
			$res = @xpath_eval($xpc, $path);

			if (count($res->nodeset) == 0)
			{
				$error = "Could not find a table root node";
			}

			if (empty ($error)) 
			{
				for($i = 0; $i < count($res->nodeset); $i++)
				{
					$node = $res->nodeset[$i];

					$this->importTableAttributes ($node);

					if ($node->has_child_nodes())
					{
						foreach($node->child_nodes() as $n)
						{
							if ($n->node_type() == XML_ELEMENT_NODE &&
							strcasecmp($n->node_name (), "tr") == 0)
							{

								$this->importRow ($lng, $n);
							}
						}
					}
				}				
			}
			$dom->free ();
		}
		if (is_array($error)) {
			$errmsg = "";
			foreach ($error as $errorline) {    # Loop through all errors
				$errmsg .=  "[" . $errorline['line'] . ", " . $errorline['col'] . "]: ".$errorline['errormessage']." at Node '". $errorline['nodename'] . "'<br />";
			}
		}else
		{
			$errmsg = $error;
		}
		
		if (empty ($errmsg)) {
			return true;
		}
		
		$_SESSION["message"] = $errmsg;
		return false;
	}
	
	/**
	* Set first row td style
	*/
	function setFirstRowStyle($a_class)
	{
		$childs = $this->tab_node->child_nodes();
		foreach($childs as $child)
		{
			if ($child->node_name() == "TableRow")
			{
				$gchilds = $child->child_nodes();
				foreach($gchilds as $gchild)
				{
					if ($gchild->node_name() == "TableData")
					{
						$gchild->set_attribute("Class", $a_class);
					}
				}
				return;
			}
		}
	}
	
	/**
	* Set Style Class of table
	*
	* @param	string	$a_class		class
	*/
	function setClass($a_class)
	{
		$this->setTableAttribute("Class", $a_class);
	}

	/**
	* Get characteristic of section.
	*
	* @return	string		characteristic
	*/
	function getClass()
	{
		return $this->getTableAttribute("Class");
	}

	/**
	* Set template
	*
	* @param	string	$a_template		template
	*/
	function setTemplate($a_template)
	{
		$this->setTableAttribute("Template", $a_template);
	}

	/**
	* Get template
	*
	* @return	string		template
	*/
	function getTemplate()
	{
		return $this->getTableAttribute("Template");
	}

	/**
	* Set header rows
	*
	* @param	string		number of header rows
	*/
	function setHeaderRows($a_nr)
	{
		$this->setTableAttribute("HeaderRows", $a_nr);
	}

	/**
	* Get header rows
	*
	* @return	string		number of header rows
	*/
	function getHeaderRows()
	{
		return $this->getTableAttribute("HeaderRows");
	}

	/**
	* Set footer rows
	*
	* @param	string		number of footer rows
	*/
	function setFooterRows($a_nr)
	{
		$this->setTableAttribute("FooterRows", $a_nr);
	}

	/**
	* Get footer rows
	*
	* @return	string		number of footer rows
	*/
	function getFooterRows()
	{
		return $this->getTableAttribute("FooterRows");
	}

	/**
	* Set header cols
	*
	* @param	string		number of header cols
	*/
	function setHeaderCols($a_nr)
	{
		$this->setTableAttribute("HeaderCols", $a_nr);
	}

	/**
	* Get header cols
	*
	* @return	string		number of header cols
	*/
	function getHeaderCols()
	{
		return $this->getTableAttribute("HeaderCols");
	}

	/**
	* Set footer cols
	*
	* @param	string		number of footer cols
	*/
	function setFooterCols($a_nr)
	{
		$this->setTableAttribute("FooterCols", $a_nr);
	}

	/**
	* Get footer cols
	*
	* @return	string		number of footer cols
	*/
	function getFooterCols()
	{
		return $this->getTableAttribute("FooterCols");
	}

	/**
	* Set attribute of table tag
	*
	* @param	string		attribute name
	* @param	string		attribute value
	*/
	protected function setTableAttribute($a_attr, $a_value)
	{
		if (!empty($a_value))
		{
			$this->tab_node->set_attribute($a_attr, $a_value);
		}
		else
		{
			if ($this->tab_node->has_attribute($a_attr))
			{
				$this->tab_node->remove_attribute($a_attr);
			}
		}
	}

	/**
	* Get table tag attribute
	*
	* @return	string		attribute name
	*/
	function getTableAttribute($a_attr)
	{
		if (is_object($this->tab_node))
		{
			return  $this->tab_node->get_attribute($a_attr);
		}
	}

}

?>
