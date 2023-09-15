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
* QTI itemfeedback class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIItemfeedback
{
    public const VIEW_ALL = "1";
    public const VIEW_ADMINISTRATOR = "2";
    public const VIEW_ADMINAUTHORITY = "3";
    public const VIEW_ASSESSOR = "4";
    public const VIEW_AUTHOR = "5";
    public const VIEW_CANDIDATE = "6";
    public const VIEW_INVIGILATORPROCTOR = "7";
    public const VIEW_PSYCHOMETRICIAN = "8";
    public const VIEW_SCORER = "9";
    public const VIEW_TUTOR = "10";

    public ?string $view = null;
    public ?string $ident = null;
    public ?string $title = null;
    /** @var ilQTIFlowmat[] */
    public array $flow_mat = [];
    /** @var ilQTIMaterial[] */
    public array $material = [];

    public function setView(string $a_view): void
    {
        switch (strtolower($a_view)) {
            case "1":
            case "all":
                $this->view = self::VIEW_ALL;
                break;
            case "2":
            case "administrator":
                $this->view = self::VIEW_ADMINISTRATOR;
                break;
            case "3":
            case "adminauthority":
                $this->view = self::VIEW_ADMINAUTHORITY;
                break;
            case "4":
            case "assessor":
                $this->view = self::VIEW_ASSESSOR;
                break;
            case "5":
            case "author":
                $this->view = self::VIEW_AUTHOR;
                break;
            case "6":
            case "candidate":
                $this->view = self::VIEW_CANDIDATE;
                break;
            case "7":
            case "invigilatorproctor":
                $this->view = self::VIEW_INVIGILATORPROCTOR;
                break;
            case "8":
            case "psychometrician":
                $this->view = self::VIEW_PSYCHOMETRICIAN;
                break;
            case "9":
            case "scorer":
                $this->view = self::VIEW_SCORER;
                break;
            case "10":
            case "tutor":
                $this->view = self::VIEW_TUTOR;
                break;
        }
    }

    public function getView(): ?string
    {
        return $this->view;
    }

    public function setIdent(string $a_ident): void
    {
        $this->ident = $a_ident;
    }

    public function getIdent(): ?string
    {
        return $this->ident;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function addFlowMat(ilQTIFlowmat $a_flow_mat): void
    {
        $this->flow_mat[] = $a_flow_mat;
    }

    public function addMaterial(ilQTIMaterial $a_material): void
    {
        $this->material[] = $a_material;
    }
}
