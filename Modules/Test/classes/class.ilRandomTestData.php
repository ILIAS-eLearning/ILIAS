<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
