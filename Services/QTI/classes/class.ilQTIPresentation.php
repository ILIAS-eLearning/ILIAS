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
* QTI presentation class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIPresentation
{
    public ?string $label = null;
    public ?string $xmllang = null;
    public ?string $x0 = null;
    public ?string $y0 = null;
    public ?string $width = null;
    public ?string $height = null;

    /** @var ilQTIMaterial[] */
    public array $material = [];

    /**
     * @var ilQTIResponse[]
     */
    public array $response = [];

    /**
     * @var array{type: string, index: int}[]
     */
    public array $order = [];

    public function setLabel(string $a_label): void
    {
        $this->label = $a_label;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setXmllang(string $a_xmllang): void
    {
        $this->xmllang = $a_xmllang;
    }

    public function getXmllang(): ?string
    {
        return $this->xmllang;
    }

    public function setX0(string $a_x0): void
    {
        $this->x0 = $a_x0;
    }

    public function getX0(): ?string
    {
        return $this->x0;
    }

    public function setY0(string $a_y0): void
    {
        $this->y0 = $a_y0;
    }

    public function getY0(): ?string
    {
        return $this->y0;
    }

    public function setWidth(string $a_width): void
    {
        $this->width = $a_width;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function setHeight(string $a_height): void
    {
        $this->height = $a_height;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function addMaterial(ilQTIMaterial $a_material): void
    {
        $count = array_push($this->material, $a_material);
        $this->order[] = array("type" => "material", "index" => $count - 1);
    }

    public function addResponse(ilQTIResponse $a_response): void
    {
        $count = array_push($this->response, $a_response);
        $this->order[] = array("type" => "response", "index" => $count - 1);
    }
}
