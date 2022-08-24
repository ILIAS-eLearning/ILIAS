<?php

declare(strict_types=1);

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
* QTI render hotspot class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIRenderHotspot
{
    public const SHOWDRAW_NO = "1";
    public const SHOWDRAW_YES = "2";

    public string $showdraw = self::SHOWDRAW_NO;
    public ?string $minnumber = null;
    public ?string $maxnumber = null;
    /** @var ilQTIResponseLabel[] */
    public array $response_labels = [];
    /** @var ilQTIMaterial[] */
    public array $material = [];

    public function setShowdraw(string $a_showdraw): void
    {
        switch (strtolower($a_showdraw)) {
            case "1":
            case "no":
                $this->showdraw = self::SHOWDRAW_NO;
                break;
            case "2":
            case "yes":
                $this->showdraw = self::SHOWDRAW_YES;
                break;
        }
    }

    public function getShowdraw(): string
    {
        return $this->showdraw;
    }

    public function setMinnumber(string $a_minnumber): void
    {
        $this->minnumber = $a_minnumber;
    }

    public function getMinnumber(): ?string
    {
        return $this->minnumber;
    }

    public function setMaxnumber(string $a_maxnumber): void
    {
        $this->maxnumber = $a_maxnumber;
    }

    public function getMaxnumber(): ?string
    {
        return $this->maxnumber;
    }

    public function addResponseLabel(ilQTIResponseLabel $a_response_label): void
    {
        $this->response_labels[] = $a_response_label;
    }

    public function addMaterial(ilQTIMaterial $a_material): void
    {
        $this->material[] = $a_material;
    }
}
