<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents a random test input property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ModulesTest
*/
class ilRandomTestData
{
    protected $data = array();
    
    /**
    * Constructor
    *
    * @param	string	$a_count	Question count
    * @param	string	$a_qpl	Questionpool id
    */
    public function __construct($a_count = "", $a_qpl = "")
    {
        $this->data = array('count' => $a_count, 'qpl' => $a_qpl);
    }

    public function __get($property)
    {
        switch ($property) {
            case 'count':
                if ((strlen($this->data[$property]) == 0) || (!is_numeric($this->data[$property]))) {
                    return 0;
                }
                return $this->data[$property];
                break;
            case 'qpl':
                return $this->data[$property];
                break;
            default:
                return null;
                break;
        }
    }
    
    public function __set($property, $value)
    {
        switch ($property) {
            case 'count':
            case 'qpl':
                $this->data[$property] = $value;
                break;
            default:
                break;
        }
    }
}
