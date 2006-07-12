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
   * Class to handle XML ResultSets
   *
   * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
   * @version $Id: class.ilXMLResultSet.php,v 1.5 2006/05/23 23:09:06 hschottm Exp $
   *
   * @package ilias
   */

include_once './webservice/soap/classes/class.ilXMLResultSetColumn.php';
include_once './webservice/soap/classes/class.ilXMLResultSetRow.php';

class ilXMLResultSet
{
		var $colspecs = array();
		var $rows = array();


		function ilXMLResultSet ()
		{
		}

		/**
		 * create a new column with columnname and attach it to column list
		 *
		 * @param String $columname
		 */
		function addColumn($columname)
		{
			$this->colspecs [$columname] = new ilXMLResultSetColumn (count($this->colspecs), $columname);
		}

		/**
		 * return array of ilXMLResultSetColumn
		 *
		 * @return array
		 */
		function getColSpecs ()
		{
			return $this->colspecs;
		}

		/**
		 * return array of ilXMLResultSetRow
		 *
		 * @return array
		 */
		function getRows () {
			return $this->rows;
		}

		/**
		 * add row object
		 *
		 * @param ilXMLResultSetRow $row
		 */
		function addRow (&$row) {
			$this->rows [] = $row;
		}

}
?>