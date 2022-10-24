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
* QTI material class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIMaterial
{
    public ?string $label = null;
    public int $flow = 0;

    /**
     * @var array{material: ilQTIMattext|ilQTIMatimage|ilQTIMatapplet, type: string}[]
     */
    public array $materials = [];

    public function addMattext(ilQTIMattext $a_mattext): void
    {
        $this->materials[] = array("material" => $a_mattext, "type" => "mattext");
    }

    public function addMatimage(ilQTIMatimage $a_matimage): void
    {
        $this->materials[] = array("material" => $a_matimage, "type" => "matimage");
    }

    public function addMatapplet(ilQTIMatapplet $a_matapplet): void
    {
        $this->materials[] = array("material" => $a_matapplet, "type" => "matapplet");
    }

    public function getMaterialCount(): int
    {
        return count($this->materials);
    }

    /**
     * @return false|array{material: ilQTIMattext|ilQTIMatimage|ilQTIMatapplet, type: string}
     */
    public function getMaterial(int $a_index)
    {
        if (array_key_exists($a_index, $this->materials)) {
            return $this->materials[$a_index];
        }

        return false;
    }

    public function setFlow(int $a_flow): void
    {
        $this->flow = $a_flow;
    }

    public function getFlow(): int
    {
        return $this->flow;
    }

    public function setLabel(string $a_label): void
    {
        $this->label = $a_label;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }
}
