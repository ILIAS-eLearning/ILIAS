<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 *
 * @author Michael Jansen <mjansen@databay.de>
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
