<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

/**
* Class ilTableGUI
*
* HTML table component
*
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*
* @package	ilias-core
*/
class ilTableGUI
{
	var $title;					// table title name
	var $icon;					// table title icon
	var $icon_alt;				// table title icon alt text

	var $help_page;				// table help name
	var $help_icon;				// table help icon
	var $help_icon_alt;			// table help icon alt text

	var $header_names;			// titles of header columns
	var $header_vars;			// var names of header columns (i.e. for database order column)

	var $data;					// table content
	
	var $column_count;			// no. of columns (based on element count of $this->header array)
	var $column_width;			// column width of each column (used in order until max. column from column count is reached)
								// any exceeding values are ignored
	var $max_count;				// max. count of database query
	var $limit;					// max. count of dataset per page
	var $offset;				// dataset offset
	var $order_column;			// order column
	var $order_direction;		// order direction
	
	var $footer_style;			// css format for links
	var	$footer_previous;		// value of previous link
	var	$footer_next;			// value of next link
	
	// default settings for enabled/disabled table modules 
	var $enabled = array(
							"title"			=>	true,
							"icon"			=>	true,
							"help"			=>	true,
							"content"		=>	true,
							"action"		=>	false,
							"footer"		=>	true,
							"linkbar"		=>	true,
							"numinfo"		=>	true
						);
	
	/**
	* Constructor
	* @access	public
	*/
	function ilTableGUI($a_data = 0)
	{
		global $ilias, $tpl, $lng;
		
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		
		$this->setData($a_data);
	}
	
	/**
	* set table data
	* @access	public
	* @param	array	table data
	*/
	function setData($a_data)
	{
		if (is_array($a_data))
		{
			$this->data = $a_data;
		}	
	}

	/**
	* set table title
	* @access	public
	* @param	string	table title
	* @param	string	file name of title icon
	* @param	string	alternative text for title icon
	*/
	function setTitle($a_title,$a_icon = 0,$a_icon_alt = 0)
	{
		$this->title = $a_title;
		$this->icon = $a_icon;
		$this->icon_alt = $a_icon_alt;

		if (!$this->icon)
		{
			$this->enabled["icon"] = false;
			
			return;
		}

		if (!$this->icon_alt)
		{
			$this->icon_alt = $this->icon;
		}
	}
	
	/**
	* set table help page
	* @access	public
	* @param	string	help page file name
	* @param	string	file name of help icon
	* @param	string	alternative text for help icon
	*/
	function setHelp($a_help_page,$a_help_icon,$a_help_icon_alt = 0)
	{
		$this->help_page = $a_help_page;
		$this->help_icon = $a_help_icon;
		$this->help_icon_alt = $a_help_icon_alt;

		if (!$this->help_icon_alt)
		{
			$this->help_icon_alt = $this->help_icon;
		}
	}
	
	/**
	* set table header names
	* @access	public
	* @param	array	table header names
	*/
	function setHeaderNames($a_header_names)
	{
		$this->header_names = $a_header_names;
		$this->column_count = count($this->header_names);
	}

	/**
	* set table header vars
	* @access	public
	* @param	array	table header vars
	* @param	array	additional link params
	*/
	function setHeaderVars($a_header_vars,$a_header_params = 0)
	{
		$this->header_vars = $a_header_vars;
		
		if ($a_header_params == 0 or !is_array($a_header_params))
		{
			$this->link_params = "";
		}
		else
		{
			$this->header_params = $a_header_params;	// temp. solution for linkbar

			foreach ($a_header_params as $key => $val)
			{
				$this->link_params .= $key."=".$val."&";
			}
		}
	}
	
	/**
	* set table column widths
	* @access	public
	* @param	array	column widths
	*/
	function setColumnWidth($a_column_width)
	{
		$this->column_width = $a_column_width;
	}
	
	/**
	* set one table column width
	* @access	public
	* @param	string	column width
	* @param	integer	column number
	*/
	function setOneColumnWidth($a_column_width,$a_column_number)
	{
		$this->column_width[$a_column_number] = $a_column_width;
	}

	/**
	* set max. count of database query
	* @access	public
	* @param	integer	max_count
	*/
	function setMaxCount($a_max_count)
	{
		$this->max_count = $a_max_count;
	}
	
	/**
	* set max. datasets displayed per page
	* @access	public
	* @param	integer	limit
	*/
	function setLimit($a_limit)
	{
		$this->limit = $a_limit;
	}
	
	/**
	* set dataset offset
	* @access	public
	* @param	integer	offset
	*/
	function setOffset($a_offset)
	{
		$this->offset = $a_offset;
	}
	
	/**
	* set order column
	* @access	public
	* @param	string	order column
	*/
	function setOrderColumn($a_order_column = 0)
	{
		// set default sort column to first column
		if (empty($a_order_column))
		{
			$this->order_column = 0;
			return;
		}

		$this->order_column = array_search($a_order_column,$this->header_vars);

		if ($this->order_column === false)
		{
			// if not found, set default sort column to first column
			$this->order_column = 0;
		}
	}

	/**
	* set order direction
	* @access	public
	* @param	string	order direction
	*/
	function setOrderDirection($a_order_direction)
	{
		if ($a_order_direction == "desc")
		{
			$this->order_direction = "desc";
			$this->sort_order = "asc";
		}
		else
		{
			$this->order_direction = "asc"; // set default sort order to "ASC"
			$this->sort_order = "desc";
		}
	}

	/**
	* set order direction
	* @access	public
	* @param	string	css format for links
	* @param	string	value of previous link
	* @param	string	value of next link
	*/
	function setFooter($a_style,$a_previous,$a_next)
	{
		if (!is_bool($a_numinfo))
		{
			$a_numinfo = true;
		}
		
		$this->footer_style = $a_style;
		$this->footer_previous = $a_previous;
		$this->footer_next = $a_next;
	}

	/**
	* enables particular modules of table
	* @access	public
	* @param	string	module name
	*/
	function enable($a_module_name)
	{
		if (!in_array($a_module_name,array_keys($this->enabled)))
		{
			return false;
		}

		$this->enabled[$a_module_name] = true;
	} 

	/**
	* diesables particular modules of table
	* @access	public
	* @param	string	module name
	*/
	function disable($a_module_name)
	{
		if (!in_array($a_module_name,array_keys($this->enabled)))
		{
			return false;
		}

		$this->enabled[$a_module_name] = false;
	}
	
	/**
	* render table 
	* @access	public
	*/
	function render()
	{
		// table title icon
		if ($this->enabled["icon"] && $this->enabled["title"])
		{
			$this->tpl->setCurrentBlock("tbl_header_title_icon");
			$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath($this->icon));
			$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->icon_alt);
			$this->tpl->parseCurrentBlock();
		}
		
		// table title help
		if ($this->enabled["help"] && $this->enabled["title"])
		{
			$this->tpl->setCurrentBlock("tbl_header_title_help");
			$this->tpl->setVariable("TBL_HELP_IMG",ilUtil::getImagePath($this->help_icon));
			$this->tpl->setVariable("TBL_HELP_LINK",$this->help_page);
			$this->tpl->setVariable("TBL_HELP_IMG_ALT",$this->help_icon_alt);
			$this->tpl->parseCurrentBlock();
		}
			
		// table title
		if ($this->enabled["title"])
		{
			$this->tpl->setCurrentBlock("tbl_header_title");
			$this->tpl->setVariable("COLUMN_COUNT",$this->column_count);
			$this->tpl->setVariable("TBL_TITLE",$this->title);
			$this->tpl->parseCurrentBlock();
		}

		// table header
		foreach ($this->header_names as $key => $tbl_header_cell)
		{
			if ($key == $this->order_column)
			{ 
				$this->tpl->setCurrentBlock("tbl_order_image");
				$this->tpl->setVariable("IMG_ORDER_DIR",ilUtil::getImagePath($this->order_direction."_order.png"));
				$this->tpl->parseCurrentBlock();
			}
		
			$this->tpl->setCurrentBlock("tbl_header_cell");
			$this->tpl->setVariable("TBL_HEADER_CELL",$tbl_header_cell);
			
			// only set width if a value is given for that column
			if ($this->column_width[$key])
			{
				$this->tpl->setVariable("TBL_COLUMN_WIDTH"," width=\"".$this->column_width[$key]."\"");
			}

			$this->tpl->setVariable("TBL_ORDER_ALT",$this->lng->txt("sort_by_this_column"));
		
			$order_dir = "ASC";
		
			if ($key == $this->order_column)
			{ 
				$order_dir = $this->sort_order;
				$this->tpl->setVariable("TBL_ORDER_ALT",$this->lng->txt("change_sort_direction"));
			}
		
			$this->tpl->setVariable("TBL_ORDER_LINK",basename($_SERVER["PHP_SELF"])."?".$this->link_params."sort_by=".$this->header_vars[$key]."&sort_order=".$order_dir."&offset=".$this->offset);
			$this->tpl->parseCurrentBlock();
		}

		// table data
		// the table content may be skipped to use an individual template blockfile
		// To do so don't set $this->data and parse your table content by yourself
		// The template block name for the blockfile MUST be 'TBL_CONTENT'
		if ($this->enabled["content"] && is_array($this->data))
		{
			$count = 0;
					
			foreach ($this->data as $tbl_content_row)
			{
				foreach ($tbl_content_row as $key => $tbl_content_cell)
				{
					$this->tpl->setCurrentBlock("tbl_content_cell");
					$this->tpl->setVariable("TBL_CONTENT_CELL",$tbl_content_cell);
					$this->tpl->parseCurrentBlock();
				}
			
				$this->tpl->setCurrentBlock("tbl_content_row");
				$rowcolor = ilUtil::switchColor($count,"tblrow2","tblrow1");
				$this->tpl->setVariable("ROWCOLOR", $rowcolor);
				$this->tpl->parseCurrentBlock();
			
				$count++;
			}
		}

		// table action row
		if ($this->enabled["action"] && is_array($this->data))
		{
		
		
		}

		// table footer numinfo
		if ($this->enabled["numinfo"] && $this->enabled["footer"])
		{

			$start = $this->offset + 1;				// compute num info
			$end = $this->offset + $this->limit;
			
			if ($end > $this->max_count)
			{
				$end = $this->max_count;
			}
			
			$numinfo = "(".$this->lng->txt("dataset")." ".$start." - ".$end." ".$this->lng->txt("of")." ".$this->max_count.")";
	
			$this->tpl->setCurrentBlock("tbl_footer_numinfo");
			$this->tpl->setVariable("NUMINFO", $numinfo);
			$this->tpl->parseCurrentBlock();
		}

		// table footer linkbar
		if ($this->enabled["linkbar"] && $this->enabled["footer"])
		{
			$params = array(
							"sort_by"		=> $this->header_vars[$this->order_column],
							"sort_order"	=> $this->order_direction
							);
			$params = array_merge($this->header_params,$params);
			
			$layout = array(
							"link"	=> $this->footer_style,
							"prev"	=> $this->footer_previous,
							"next"	=> $this->footer_next,
							);
			
			$linkbar = ilUtil::Linkbar(basename($_SERVER["PHP_SELF"]),$this->max_count,$this->limit,$this->offset,$params,$layout);
		
			$this->tpl->setCurrentBlock("tbl_footer_linkbar");
			$this->tpl->setVariable("LINKBAR", $linkbar);
			$this->tpl->parseCurrentBlock();
		}
						
		// table footer
		if ($this->enabled["footer"])
		{
			$this->tpl->setCurrentBlock("tbl_footer");
			$this->tpl->setVariable("COLUMN_COUNT",$this->column_count);
			$this->tpl->parseCurrentBlock();
		}
	}
}
?>
