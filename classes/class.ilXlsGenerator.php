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
* Class ilXlsGenerator
*
* @author Muzaffar Altaf <maltaf@tzi.de>
* $Id$
*
* @extends PEAR
* @package ilias-core
* @package assessment
*/

class  ilXlsGenerator extends PEAR
{


	var $xls_data = "";
	var $error = "";
	var $download = "true";


	/**
	* Constructor, writting begin of  Excel file
	* @access public
    * @param $download boolean  true, if ask for "Save/Open", false -
    *    if open Excel (application/x-exel).
    */
    function ilXlsGenerator($download = "true")
    {
        $this->download = $download;
        $this->Begin();
    }


   /**
    * ilXlsGenerator begin of file header
    * Send the header to client
    *
    * @param $filename string - name of file, for save
    * (actually, if $download = true)
    */
    function _Header($filename)
    {
        header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header ("Cache-Control: no-cache, must-revalidate");
        header ("Pragma: no-cache");
        if ($this->download) {
            header ("Content-type: application/x-msexcel");
            header ("Content-Disposition: attachment; filename=$filename" );
        } else {
            header ("Content-type: application/x-msexcel");
        }
            header ("Content-Description: PHP Generated Data" );
    }

   /**
    * Begin of Excel File.
    */
    function Begin()
    {
        $str = pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
        $this->xls_data = $str;
        return $str;
    }

   /**
    * End of Excel File (binary)
    */
    function EOF()
    {
        $str = pack("ss", 0x0A, 0x00);
        $this->xls_data .=  $str;
        return $str;
    }

   /**
    * Function for writting number (double) into row $Row,
    * and column $Col.
    *
    * @param $Row integer - Row
    * @param $Col integer - Column
    * @param $Value number - value
    *
    */
    function WriteNumber($Row, $Col, $Value)
    {
        $str = pack("sssss", 0x203, 14, $Row, $Col, 0x0);
        $str .= pack("d", $Value);
        $this->xls_data .= $str;
        return $str;
    }


   /**
    * Function for writting label(string) into row $Row,
    * and column $Col.
    * @param $Row integer - Row
    * @param $Col integer - Column
    * @param $Value number - value
    *
    */
    function WriteLabel($Row, $Col, $Value )
    {
        $L = strlen($Value);
        $str = pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
        $str .= $Value;
        $this->xls_data .=  $str;
        return $str;
    }

   /**
    * Function to send file to client(browser)
    *
    * @param $filename string -  name of file (actually, if $download = true)
    *
    */
    function SendFile($filename = "test.xls")
    {
        $this->_Header($filename);
        echo $this->xls_data;
        echo $this->EOF();
    }

   /**
    * Function to get Excel (binary) data
    *
    * @param $eof boolean - id true, returnet data will with the end of Excel File (bin)
    *
    */
    function GetData($eof = "true")
    {
        if ($eof) return $xls_data . $this->EOF();
        else return $xls_data;
    }


   /**
    * Function to write Excel-data to $file
    *
    * @param $efile string - File name.
    *
    */
    function toFile($file = "test.xls")
    {
        $fp = @fopen($file,"w");
        if (is_resource($fp)) {
            fwrite($fp, $this->xls_data);
            fclose($fp);
            return true;
        } else {
            return $this->raiseError("Can't access to '$file' for writting!",-1);
        }
    }

} // End of class ilXlsGenerator.
