<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilSearchGUI
*
* Base class for all search classes
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias-search
*
*/

class ilAbstractSearch
{
    /*
     * instance of db object
     */
    public $db = null;
    /*
     * instance of query parser
     */
    public $query_parser = null;
    
    /*
     * instance of result obj
     */
    public $search_result = null;

    /*
     * List of all searchable objects
     */
    public $object_types = array('cat','dbk','crs','fold','frm','grp','lm','sahs','glo','mep','htlm','exc','file','qpl','tst','svy','spl',
                         'chat','webr','mcst','sess','pg','st','wiki','book', 'copa');
                         
    private $id_filter = array();


    /**
    * Constructor
    * @access public
    */
    public function __construct($qp_obj)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->query_parser = $qp_obj;
        $this->db = $ilDB;

        include_once 'Services/Search/classes/class.ilSearchResult.php';

        $this->search_result = new ilSearchResult();
    }

    /**
    * Set fields to search
    * @param array Array of table field (e.g array('title','description'))
    * @access public
    */
    public function setFields($a_fields)
    {
        $this->fields = $a_fields;
    }

    /**
    * Get fields to search
    * @return array array of search fields. E.g. array(title,description)
    * @access public
    */
    public function getFields()
    {
        return $this->fields ? $this->fields : array();
    }

    /**
    * set object type to search in
    * @param array Array of object types (e.g array('lm','st','pg','dbk'))
    * @access public
    */
    public function setFilter($a_filter)
    {
        if (is_array($a_filter)) {
            $this->object_types = $a_filter;
        }
    }
    
    /**
     * Set id filter
     * Filters search by given object id
     * @return
     */
    public function setIdFilter($a_id_filter)
    {
        $this->id_filter = $a_id_filter;
    }
    
    /**
     * Get Id filter
     * @return
     */
    public function getIdFilter()
    {
        return (array) $this->id_filter;
    }

    /**
    * Append object type to filter
    * @param string obj_type e.g. 'role'
    * @access public
    */
    public function appendToFilter($a_type)
    {
        if (is_array($this->object_types)) {
            if (in_array($a_type, $this->object_types)) {
                return false;
            }
        }
        $this->object_types[] = $a_type;
        
        return true;
    }


    /**
    * get object type to search in
    * @param array Array of object types (e.g array('lm','st','pg','dbk'))
    * @access public
    */
    public function getFilter()
    {
        return $this->object_types ? $this->object_types : array();
    }

    /**
    * build locate string in case of AND search
    * @return string
    * @access public
    */
    public function __createLocateString()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->query_parser->getCombination() == 'or') {
            return '';
        }
        if (count($this->fields) > 1) {
            foreach ($this->fields as $field) {
                $tmp_fields[] = array($field,'text');
            }
            $complete_str = $ilDB->concat($tmp_fields);
            
        /*
        $complete_str = 'CONCAT(';
        $complete_str .= implode(',',$this->fields);
        $complete_str .= ')';
        */
        } else {
            $complete_str = $this->fields[0];
        }

        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            $locate .= ',';
            $locate .= $ilDB->locate($ilDB->quote($word, 'text'), $complete_str);
            $locate .= (' found' . $counter++);
            $locate .= ' ';
            #$locate .= (", LOCATE('".$word."',".$complete_str.") ");
            #$locate .= ("as found".$counter++." ");
        }
        
        return $locate;
    }

    public function __prepareFound(&$row)
    {
        if ($this->query_parser->getCombination() == 'or') {
            return array();
        }
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            $res_found = "found" . $counter++;
            $found[] = $row->$res_found;
        }
        return $found ? $found : array();
    }

    public function performSearch()
    {
        echo "Should be overwritten.";
    }
}
