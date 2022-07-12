<?php declare(strict_types=1);

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
 ********************************************************************
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
    public array $arr_not = [];
    /** @var int[] */
    public array $arr_and = [];
    /** @var int[] */
    public array $arr_or = [];
    /** @var ilQTIResponseVar[] */
    public array $varequal = [];
    /** @var ilQTIResponseVar[] */
    public array $varlt = [];
    /** @var ilQTIResponseVar[] */
    public array $varlte = [];
    /** @var ilQTIResponseVar[] */
    public array $vargt = [];
    /** @var ilQTIResponseVar[] */
    public array $vargte = [];
    /** @var ilQTIResponseVar[] */
    public array $varsubset = [];
    /** @var ilQTIResponseVar[] */
    public array $varinside = [];
    /** @var ilQTIResponseVar[] */
    public array $varsubstring = [];
    /** @var array{field: string, index: int} */
    public array $order = [];
    
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
            case ilQTIResponseVar::RESPONSEVAR_EQUAL:
                $this->addVarequal($a_responsevar);
                break;
            case ilQTIResponseVar::RESPONSEVAR_LT:
                $this->addVarlt($a_responsevar);
                break;
            case ilQTIResponseVar::RESPONSEVAR_LTE:
                $this->addVarlte($a_responsevar);
                break;
            case ilQTIResponseVar::RESPONSEVAR_GT:
                $this->addVargt($a_responsevar);
                break;
            case ilQTIResponseVar::RESPONSEVAR_GTE:
                $this->addVargte($a_responsevar);
                break;
            case ilQTIResponseVar::RESPONSEVAR_SUBSET:
                $this->addVarsubset($a_responsevar);
                break;
            case ilQTIResponseVar::RESPONSEVAR_INSIDE:
                $this->addVarinside($a_responsevar);
                break;
            case ilQTIResponseVar::RESPONSEVAR_SUBSTRING:
                $this->addVarsubstring($a_responsevar);
                break;
        }
    }
}
