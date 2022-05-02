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
* QTI response class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIResponse
{
    public const RT_RESPONSE_LID = "1";
    public const RT_RESPONSE_XY = "2";
    public const RT_RESPONSE_STR = "3";
    public const RT_RESPONSE_NUM = "4";
    public const RT_RESPONSE_GRP = "5";

    public const R_CARDINALITY_SINGLE = "1";
    public const R_CARDINALITY_MULTIPLE = "2";
    public const R_CARDINALITY_ORDERED = "3";

    public const RTIMING_NO = "1";
    public const RTIMING_YES = "2";

    public const NUMTYPE_INTEGER = "1";
    public const NUMTYPE_DECIMAL = "2";
    public const NUMTYPE_SCIENTIFIC = "3";

    public int $flow = 0;
    /** @var int|string */
    public $response_type;
    public ?string $ident = null;
    public string $rcardinality = '';

    /**
     * @var ilQTIRenderChoice|ilQTIRenderHotspot|ilQTIRenderFib|null
     */
    public $render_type = null;
    public ?ilQTIMaterial $material1 = null;
    public ?ilQTIMaterial $material2 = null;
    public ?string $rtiming = null;
    public ?string $numtype = null;

    /**
     * @param string|int $a_response_type
     */
    public function __construct($a_response_type = 0)
    {
        $this->response_type = $a_response_type;
    }

    /**
     * @param string|int $a_responsetype
     */
    public function setResponsetype($a_responsetype) : void
    {
        $this->response_type = $a_responsetype;
    }

    /**
     * @return int|string
     */
    public function getResponsetype()
    {
        return $this->response_type;
    }

    public function setIdent(string $a_ident) : void
    {
        $this->ident = $a_ident;
    }

    public function getIdent() : ?string
    {
        return $this->ident;
    }

    public function setRCardinality(string $a_rcardinality) : void
    {
        switch (strtolower($a_rcardinality)) {
            case "single":
            case "1":
                $this->rcardinality = self::R_CARDINALITY_SINGLE;
                break;
            case "multiple":
            case "2":
                $this->rcardinality = self::R_CARDINALITY_MULTIPLE;
                break;
            case "ordered":
            case "3":
                $this->rcardinality = self::R_CARDINALITY_ORDERED;
                break;
        }
    }

    public function getRCardinality() : string
    {
        return $this->rcardinality;
    }

    public function setRTiming(string $a_rtiming) : void
    {
        switch (strtolower($a_rtiming)) {
            case "no":
            case "1":
                $this->rtiming = self::RTIMING_NO;
                break;
            case "yes":
            case "2":
                $this->rtiming = self::RTIMING_YES;
                break;
        }
    }

    public function getRTiming() : ?string
    {
        return $this->rtiming;
    }

    public function setNumtype(string $a_numtype) : void
    {
        switch (strtolower($a_numtype)) {
            case "integer":
            case "1":
                $this->numtype = self::NUMTYPE_INTEGER;
                break;
            case "decimal":
            case "2":
                $this->numtype = self::NUMTYPE_DECIMAL;
                break;
            case "scientific":
            case "3":
                $this->numtype = self::NUMTYPE_SCIENTIFIC;
                break;
        }
    }

    public function getNumtype() : ?string
    {
        return $this->numtype;
    }

    /**
     * @param ilQTIRenderChoice|ilQTIRenderHotspot|ilQTIRenderFib $a_render_type
     */
    public function setRenderType($a_render_type) : void
    {
        $this->render_type = $a_render_type;
    }

    /**
     * @return ilQTIRenderChoice|ilQTIRenderHotspot|ilQTIRenderFib|null
     */
    public function getRenderType()
    {
        return $this->render_type;
    }

    public function setFlow(int $a_flow) : void
    {
        $this->flow = $a_flow;
    }

    public function getFlow() : int
    {
        return $this->flow;
    }
    
    public function setMaterial1(ilQTIMaterial $a_material) : void
    {
        $this->material1 = $a_material;
    }
    
    public function getMaterial1() : ?ilQTIMaterial
    {
        return $this->material1;
    }

    public function setMaterial2(ilQTIMaterial $a_material) : void
    {
        $this->material2 = $a_material;
    }
    
    public function getMaterial2() : ?ilQTIMaterial
    {
        return $this->material2;
    }
    
    public function hasRendering() : bool
    {
        return $this->render_type != null;
    }
}
