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
   * Row Class for XMLResultSet
   *
   * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
   * @version $Id: class.ilXMLResultSet.php,v 1.5 2006/05/23 23:09:06 hschottm Exp $
   *
   * @package ilias
   */

class ilXMLResultSetRow {
	private $columns = array();

	/**
	 * set column value
	 *
	 * @param mixed $index
	 * @param String $value
	 */
	function setValue ($index, $value)
	{
		$this->columns[$index] = $value;
	}


	/**
	 * get column array
	 *
	 * @return array
	 */
	function getColumns ()
	{
		return $this->columns;
	}

	/**
	 * add values from array
	 *
	 * @param array $values
	 */
	function setValues ($values)
	{
	    $i = 0;
	    foreach ($values as $value) {
            $this->setValue($i++, $value);
		}
	}

	/**
	 * return value for column with specified index
	 *
	 * @param int $idx
	 * @return string
	 */
	function getValue ($idx) {
		if ($idx < 0 || $idx >= count($this->columns))
			throw new Exception ("Index too small or too large");
		return $this->columns[$idx];
	}
}

?>