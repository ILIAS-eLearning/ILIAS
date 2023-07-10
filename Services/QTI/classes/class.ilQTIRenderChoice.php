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
* QTI render choice class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIRenderChoice
{
    public const SHUFFLE_NO = "0";
    public const SHUFFLE_YES = "1";

    public string $shuffle = self::SHUFFLE_NO;
    public ?string $minnumber = null;
    public ?string $maxnumber = null;
    /** @var ilQTIResponseLabel[] */
    public array $response_labels = [];
    /** @var ilQTIMaterial[] */
    public array $material = [];

    public function setShuffle(string $a_shuffle): void
    {
        switch (strtolower($a_shuffle)) {
            case "0":
            case "no":
                $this->shuffle = self::SHUFFLE_NO;
                break;
            case "1":
            case "yes":
                $this->shuffle = self::SHUFFLE_YES;
                break;
        }
    }

    public function getShuffle(): string
    {
        return $this->shuffle;
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
