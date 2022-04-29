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
* QTI material class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIMaterial
{
    public ?string $label;
    public int $flow;

    /**
     * @var array{material: ilQTIMattext|ilQTIMatimage|ilQTIMatapplet|null, type: string}[]
     */
    public array $materials;
    
    public function __construct()
    {
        $this->label = null;
        $this->flow = 0;
        $this->materials = [];
    }

    public function addMattext(?ilQTIMattext $a_mattext) : void
    {
        $this->materials[] = array("material" => $a_mattext, "type" => "mattext");
    }

    public function addMatimage(?ilQTIMatimage $a_matimage) : void
    {
        $this->materials[] = array("material" => $a_matimage, "type" => "matimage");
    }

    public function addMatapplet(?ilQTIMatapplet $a_matapplet) : void
    {
        $this->materials[] = array("material" => $a_matapplet, "type" => "matapplet");
    }

    public function getMaterialCount() : int
    {
        return count($this->materials);
    }

    /**
     * @return false|array{material: ilQTIMattext|ilQTIMatimage|ilQTIMatapplet|null, type: string}
     */
    public function getMaterial(int $a_index)
    {
        if (array_key_exists($a_index, $this->materials)) {
            return $this->materials[$a_index];
        }

        return false;
    }

    public function setFlow(int $a_flow) : void
    {
        $this->flow = $a_flow;
    }

    public function getFlow() : int
    {
        return $this->flow;
    }

    public function setLabel(string $a_label) : void
    {
        $this->label = $a_label;
    }

    public function getLabel() : ?string
    {
        return $this->label;
    }
}
