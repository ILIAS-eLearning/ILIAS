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
    public $arr_not;
    public $arr_and;
    public $arr_or;
    public $unanswered;
    public $other;
    public $varequal;
    public $varlt;
    public $varlte;
    public $vargt;
    public $vargte;
    public $varsubset;
    public $varinside;
    public $varsubstring;
    public $durequal;
    public $durlt;
    public $durlte;
    public $durgt;
    public $durgte;
    public $varextension;
    public $order;
    
    public function __construct()
    {
        $this->arr_not = array();
        $this->arr_and = array();
        $this->arr_or = array();
        $this->unanswered = array();
        $this->other = array();
        $this->varequal = array();
        $this->varlt = array();
        $this->varlte = array();
        $this->vargt = array();
        $this->vargte = array();
        $this->varsubset = array();
        $this->varinside = array();
        $this->varsubstring = array();
        $this->durequal = array();
        $this->durlt = array();
        $this->durlte = array();
        $this->durgt = array();
        $this->durgte = array();
        $this->varextension = array();
        $this->order = array();
    }
    
    public function addNot(): void
    {
        $this->arr_not[] = 1;
        $this->order[] = array("field" => "arr_not", "index" => count($this->arr_not) - 1);
    }
    
    public function addAnd(): void
    {
        $this->arr_and[] = 1;
        $this->order[] = array("field" => "arr_and", "index" => count($this->arr_and) - 1);
    }

    public function addOr(): void
    {
        $this->arr_or[] = 1;
        $this->order[] = array("field" => "arr_or", "index" => count($this->arr_or) - 1);
    }
    
    public function addUnanswered($a_unanswered): void
    {
        $this->unanswered[] = $a_unanswered;
        $this->order[] = array("field" => "unanswered", "index" => count($this->unanswered) - 1);
    }
    
    public function addOther($a_other): void
    {
        $this->other[] = $a_other;
        $this->order[] = array("field" => "other", "index" => count($this->other) - 1);
    }
    
    public function addVarequal($a_varequal): void
    {
        $this->varequal[] = $a_varequal;
        $this->order[] = array("field" => "varequal", "index" => count($this->varequal) - 1);
    }
    
    public function addVarlt($a_varlt): void
    {
        $this->varlt[] = $a_varlt;
        $this->order[] = array("field" => "varlt", "index" => count($this->varlt) - 1);
    }
    
    public function addVarlte($a_varlte): void
    {
        $this->varlte[] = $a_varlte;
        $this->order[] = array("field" => "varlte", "index" => count($this->varlte) - 1);
    }
    
    public function addVargt($a_vargt): void
    {
        $this->vargt[] = $a_vargt;
        $this->order[] = array("field" => "vargt", "index" => count($this->vargt) - 1);
    }
    
    public function addVargte($a_vargte): void
    {
        $this->vargte[] = $a_vargte;
        $this->order[] = array("field" => "vargte", "index" => count($this->vargte) - 1);
    }
    
    public function addVarsubset($a_varsubset): void
    {
        $this->varsubset[] = $a_varsubset;
        $this->order[] = array("field" => "varsubset", "index" => count($this->varsubset) - 1);
    }
    
    public function addVarinside($a_varinside): void
    {
        $this->varinside[] = $a_varinside;
        $this->order[] = array("field" => "varinside", "index" => count($this->varinside) - 1);
    }
    
    public function addVarsubstring($a_varsubstring): void
    {
        $this->varsubstring[] = $a_varsubstring;
        $this->order[] = array("field" => "varsubstring", "index" => count($this->varsubstring) - 1);
    }
    
    public function addDurequal($a_durequal): void
    {
        $this->durequal[] = $a_durequal;
        $this->order[] = array("field" => "durequal", "index" => count($this->durequal) - 1);
    }
    
    public function addDurlt($a_durlt): void
    {
        $this->durlt[] = $a_durlt;
        $this->order[] = array("field" => "durlt", "index" => count($this->durlt) - 1);
    }
    
    public function addDurlte($a_durlte): void
    {
        $this->durlte[] = $a_durlte;
        $this->order[] = array("field" => "durlte", "index" => count($this->durlte) - 1);
    }
    
    public function addDurgt($a_durgt): void
    {
        $this->durgt[] = $a_durgt;
        $this->order[] = array("field" => "durgt", "index" => count($this->durgt) - 1);
    }
    
    public function addDurgte($a_durgte): void
    {
        $this->durgte[] = $a_durgte;
        $this->order[] = array("field" => "durgte", "index" => count($this->durgte) - 1);
    }
    
    public function addVarextension($a_varextension): void
    {
        $this->varextension[] = $a_varextension;
        $this->order[] = array("field" => "varextension", "index" => count($this->varextension) - 1);
    }
    
    public function addResponseVar($a_responsevar): void
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
