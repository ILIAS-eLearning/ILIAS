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
* QTI response label class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIResponseLabel
{
    public const RSHUFFLE_NO = "1";
    public const RSHUFFLE_YES = "2";

    public const RAREA_ELLIPSE = "1";
    public const RAREA_RECTANGLE = "2";
    public const RAREA_BOUNDED = "3";

    public const RRANGE_EXACT = "1";
    public const RRANGE_RANGE = "2";

    public ?string $rshuffle = null;
    public ?string $rarea = null;
    public ?string $rrange = null;
    public ?string $labelrefid = null;
    public ?string $ident = null;
    public ?string $match_group = null;
    public ?string $match_max = null;
    /** @var ilQTIMaterial[] */
    public array $material = [];
    /** @var ilQTIFlowMat[] */
    public array $flow_mat = [];
    public ?string $content = null;

    public function setRshuffle(string $a_rshuffle) : void
    {
        switch (strtolower($a_rshuffle)) {
            case "1":
            case "no":
                $this->rshuffle = self::RSHUFFLE_NO;
                break;
            case "2":
            case "yes":
                $this->rshuffle = self::RSHUFFLE_YES;
                break;
        }
    }

    public function getRshuffle() : ?string
    {
        return $this->rshuffle;
    }

    public function setRarea(string $a_rarea) : void
    {
        switch (strtolower($a_rarea)) {
            case "1":
            case "ellipse":
                $this->rarea = self::RAREA_ELLIPSE;
                break;
            case "2":
            case "rectangle":
                $this->rarea = self::RAREA_RECTANGLE;
                break;
            case "3":
            case "bounded":
                $this->rarea = self::RAREA_BOUNDED;
                break;
        }
    }

    public function getRarea() : ?string
    {
        return $this->rarea;
    }

    public function setRrange(string $a_rrange) : void
    {
        switch (strtolower($a_rrange)) {
            case "1":
            case "excact":
                $this->rrange = self::RRANGE_EXACT;
                break;
            case "2":
            case "range":
                $this->rrange = self::RRANGE_RANGE;
                break;
        }
    }

    public function getRrange() : ?string
    {
        return $this->rrange;
    }

    public function setLabelrefid(string $a_labelrefid) : void
    {
        $this->labelrefid = $a_labelrefid;
    }

    public function getLabelrefid() : ?string
    {
        return $this->labelrefid;
    }

    public function setIdent(string $a_ident) : void
    {
        $this->ident = $a_ident;
    }

    public function getIdent() : ?string
    {
        return $this->ident;
    }
    
    public function setMatchGroup(string $a_match_group) : void
    {
        $this->match_group = $a_match_group;
    }
    
    public function getMatchGroup() : ?string
    {
        return $this->match_group;
    }
    
    public function setMatchMax(string $a_match_max) : void
    {
        $this->match_max = $a_match_max;
    }
    
    public function getMatchMax() : ?string
    {
        return $this->match_max;
    }
    
    public function addMaterial(ilQTIMaterial $a_material) : void
    {
        $this->material[] = $a_material;
    }
    
    public function addFlow_mat(ilQTIFlowMat $a_flow_mat) : void
    {
        $this->flow_mat[] = $a_flow_mat;
    }

    public function setContent(string $a_content) : void
    {
        $this->content = $a_content;
    }

    public function getContent() : ?string
    {
        return $this->content;
    }
}
