<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
* @ingroup ServicesUtilities
*/



class ilCSVReader
{
    private $ptr_file = null;
    private $data = array();
    private $separator = ';';
    private $delimiter = '""';
    private $length = 1024;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct()
    {
    }
    
    /*
     * Set Seperator
     *
     * @access public
     * @param string field seperator
     *
     */
    public function setSeparator($a_sep)
    {
        $this->separator = $a_sep;
    }
    
    /**
     * Set delimiter
     *
     * @access public
     * @param string field delimiter
     *
     */
    public function setDelimiter($a_del)
    {
        $this->delimiter = $a_del;
    }
    
    /**
     * Set length
     *
     * @access public
     * @param int length
     *
     */
    public function setLength($a_length)
    {
        $this->length = $a_length;
    }
    
    public function open($file = "")
    {
        // #16643
        $this->line_ends = ini_get("auto_detect_line_endings");
        ini_set("auto_detect_line_endings", true);
        
        return($this->ptr_file = @fopen(ilUtil::stripSlashes($file), "r"));
    }
    
    public function close()
    {
        // see open();
        ini_set("auto_detect_line_endings", $this->line_ends);
        
        return(@fclose($this->ptr_file));
    }
    
    /**
     * Get data as array from csv file
     *
     * @access public
     * @return array $this->data Data of file
     *
     */
    public function getDataArrayFromCSVFile()
    {
        $row = 0;

        while (($line = fgetcsv($this->ptr_file, $this->length, $this->separator)) !== false) {
            for ($col = 0; $col < count($line); $col++) {
                $this->data[$row][$col] = $this->unquote($line[$col]);
            }
            
            ++$row;
        }
        
        return $this->data;
    }
    
    /**
     *
     * @access private
     * @param string field value
     *
     */
    private function unquote($a_str)
    {
        return str_replace($this->delimiter . $this->delimiter, $this->delimiter, $a_str);
    }
}
