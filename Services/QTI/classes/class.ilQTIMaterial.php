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
    public $label;

    /** @var int */
    public $flow;
    public $comment;
    public $mattext;
    public $matemtext;
    public $matimage;
    public $mataudio;
    public $matvideo;
    public $matapplet;
    public $matapplication;
    public $matref;
    public $matbreak;
    public $mat_extension;

    /**
     * @var array
     */
    public $altmaterial;

    /**
     * @var array [['material' => string, 'type' => string]]
     */
    public $materials;
    
    public function __construct()
    {
        $this->flow = 0;
        $this->altmaterial = array();
        $this->materials = array();
    }

    /**
     * @param string $a_mattext
     */
    public function addMattext($a_mattext) : void
    {
        $this->materials[] = array("material" => $a_mattext, "type" => "mattext");
    }

    /**
     * @param string $a_matimage
     */
    public function addMatimage($a_matimage) : void
    {
        $this->materials[] = array("material" => $a_matimage, "type" => "matimage");
    }

    /**
     * @param string $a_matemtext
     */
    public function addMatemtext($a_matemtext) : void
    {
        $this->materials[] = array("material" => $a_matemtext, "type" => "matemtext");
    }

    /**
     * @param string $a_mataudio
     */
    public function addMataudio($a_mataudio) : void
    {
        $this->materials[] = array("material" => $a_mataudio, "type" => "mataudio");
    }

    /**
     * @param string $a_matvideo
     */
    public function addMatvideo($a_matvideo) : void
    {
        $this->materials[] = array("material" => $a_matvideo, "type" => "matvideo");
    }

    /**
     * @param string $a_matapplet
     */
    public function addMatapplet($a_matapplet) : void
    {
        $this->materials[] = array("material" => $a_matapplet, "type" => "matapplet");
    }

    /**
     * @param string $a_matapplication
     */
    public function addMatapplication($a_matapplication) : void
    {
        $this->materials[] = array("material" => $a_matapplication, "type" => "matapplication");
    }

    /**
     * @param string $a_matref
     */
    public function addMatref($a_matref) : void
    {
        $this->materials[] = array("material" => $a_matref, "type" => "matref");
    }

    /**
     * @param string $a_matbreak
     */
    public function addMatbreak($a_matbreak) : void
    {
        $this->materials[] = array("material" => $a_matbreak, "type" => "matbreak");
    }

    /**
     * @param string $a_mat_extension
     */
    public function addMat_extension($a_mat_extension) : void
    {
        $this->materials[] = array("material" => $a_mat_extension, "type" => "mat_extension");
    }

    /**
     * @param string $a_altmaterial
     */
    public function addAltmaterial($a_altmaterial) : void
    {
        $this->materials[] = array("material" => $a_altmaterial, "type" => "altmaterial");
    }
    
    public function getMaterialCount() : int
    {
        return count($this->materials);
    }

    /**
     * @param int $a_index
     */
    public function getMaterial($a_index)
    {
        if (array_key_exists($a_index, $this->materials)) {
            return $this->materials[$a_index];
        }

        return false;
    }

    /**
     * @param int $a_flow
     */
    public function setFlow($a_flow) : void
    {
        $this->flow = $a_flow;
    }

    /**
     * @return int
     */
    public function getFlow()
    {
        return $this->flow;
    }
    
    public function setLabel($a_label) : void
    {
        $this->label = $a_label;
    }
    
    public function getLabel()
    {
        return $this->label;
    }
    
    public function extractText() : string
    {
        $text = "";
        if ($this->getMaterialCount()) {
            foreach ($this->materials as $mat) {
                if ($mat["type"] === "mattext") {
                    $text .= $mat["material"];
                }
            }
        }
        return $text;
    }
}
