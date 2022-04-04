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
* QTI conditionvar class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIConditionvar
{
    /** @var int[] */
    public array $arr_not;
    /** @var int[] */
    public array $arr_and;
    /** @var int[] */
    public array $arr_or;
    /** @var ilQTIResponseVar[] */
    public array $varequal;
    /** @var ilQTIResponseVar[] */
    public array $varlt;
    /** @var ilQTIResponseVar[] */
    public array $varlte;
    /** @var ilQTIResponseVar[] */
    public array $vargt;
    /** @var ilQTIResponseVar[] */
    public array $vargte;
    /** @var ilQTIResponseVar[] */
    public array $varsubset;
    /** @var ilQTIResponseVar[] */
    public array $varinside;
    /** @var ilQTIResponseVar[] */
    public array $varsubstring;
    /** @var array{field: string, index: int} */
    public array $order;
    
    public function __construct()
    {
        $this->arr_not = [];
        $this->arr_and = [];
        $this->arr_or = [];
        $this->varequal = [];
        $this->varlt = [];
        $this->varlte = [];
        $this->vargt = [];
        $this->vargte = [];
        $this->varsubset = [];
        $this->varinside = [];
        $this->varsubstring = [];
        $this->order = [];
    }
    
    public function addNot() : void
    {
        $this->arr_not[] = 1;
        $this->order[] = array("field" => "arr_not", "index" => count($this->arr_not) - 1);
    }
    
    public function addAnd() : void
    {
        $this->arr_and[] = 1;
        $this->order[] = array("field" => "arr_and", "index" => count($this->arr_and) - 1);
    }

    public function addOr() : void
    {
        $this->arr_or[] = 1;
        $this->order[] = array("field" => "arr_or", "index" => count($this->arr_or) - 1);
    }

    public function addVarequal(ilQTIResponseVar $a_varequal) : void
    {
        $this->varequal[] = $a_varequal;
        $this->order[] = array("field" => "varequal", "index" => count($this->varequal) - 1);
    }

    public function addVarlt(ilQTIResponseVar $a_varlt) : void
    {
        $this->varlt[] = $a_varlt;
        $this->order[] = array("field" => "varlt", "index" => count($this->varlt) - 1);
    }

    public function addVarlte(ilQTIResponseVar $a_varlte) : void
    {
        $this->varlte[] = $a_varlte;
        $this->order[] = array("field" => "varlte", "index" => count($this->varlte) - 1);
    }

    public function addVargt(ilQTIResponseVar $a_vargt) : void
    {
        $this->vargt[] = $a_vargt;
        $this->order[] = array("field" => "vargt", "index" => count($this->vargt) - 1);
    }

    public function addVargte(ilQTIResponseVar $a_vargte) : void
    {
        $this->vargte[] = $a_vargte;
        $this->order[] = array("field" => "vargte", "index" => count($this->vargte) - 1);
    }

    public function addVarsubset(ilQTIResponseVar $a_varsubset) : void
    {
        $this->varsubset[] = $a_varsubset;
        $this->order[] = array("field" => "varsubset", "index" => count($this->varsubset) - 1);
    }

    public function addVarinside(ilQTIResponseVar $a_varinside) : void
    {
        $this->varinside[] = $a_varinside;
        $this->order[] = array("field" => "varinside", "index" => count($this->varinside) - 1);
    }

    public function addVarsubstring(ilQTIResponseVar $a_varsubstring) : void
    {
        $this->varsubstring[] = $a_varsubstring;
        $this->order[] = array("field" => "varsubstring", "index" => count($this->varsubstring) - 1);
    }

    public function addResponseVar(ilQTIResponseVar $a_responsevar) : void
    {
        switch ($a_responsevar->getVartype()) {
            case RESPONSEVAR_EQUAL:
                $this->addVarequal($a_responsevar);
                break;
            case RESPONSEVAR_LT:
                $this->addVarlt($a_responsevar);
                break;
            case RESPONSEVAR_LTE:
                $this->addVarlte($a_responsevar);
                break;
            case RESPONSEVAR_GT:
                $this->addVargt($a_responsevar);
                break;
            case RESPONSEVAR_GTE:
                $this->addVargte($a_responsevar);
                break;
            case RESPONSEVAR_SUBSET:
                $this->addVarsubset($a_responsevar);
                break;
            case RESPONSEVAR_INSIDE:
                $this->addVarinside($a_responsevar);
                break;
            case RESPONSEVAR_SUBSTRING:
                $this->addVarsubstring($a_responsevar);
                break;
        }
    }
}
