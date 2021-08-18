<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Helper class to generate CSV files.
 * Default field seperator is ','
 * Default string delimiter is '"'
 * Multiple "-'s will be substituted with ""
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilCSVWriter
{
    private $csv = '';
    private $separator = ',';
    private $delimiter = '"';
    private $new_line = "\n";
    private $doUTF8Decoding = false;
    
    private $first_entry = true;
    
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
    /**
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
     * Add new line
     *
     * @access public
     *
     */
    public function addRow()
    {
        $this->csv .= $this->new_line;
        $this->first_entry = true;
    }
    
    /**
    * set to true if you want to utf8 decode for output.
    *
    * @param	doUTF8Decoding boolean	if set to true file is written using a utf8decode before writing values
    *
    */
    public function setDoUTF8Decoding($doUTF8Decoding)
    {
        $this->doUTF8Decoding = $doUTF8Decoding ? true : false;
    }
    
    /**
     * Add Column. Will be quoted automatically
     *
     * @access public
     * @param string column value
     *
     */
    public function addColumn($a_col)
    {
        if (!$this->first_entry) {
            $this->csv .= $this->separator;
        }
        $this->csv .= $this->delimiter;
        $this->csv .= $this->quote($a_col);
        $this->csv .= $this->delimiter;
        $this->first_entry = false;
    }
    
    /**
     * Get CSV string
     *
     * @access public
     * @param
     *
     */
    public function getCSVString()
    {
        return $this->csv;
    }
    
    /**
     * Quote Delimiter by doubling it
     * This seems to be the standard way in Excel and Openoffice
     *
     * @access private
     * @param string field value
     *
     */
    private function quote($a_str)
    {
        return str_replace($this->delimiter, $this->delimiter . $this->delimiter, ($this->doUTF8Decoding) ? utf8_decode($a_str) : $a_str);
    }
}
