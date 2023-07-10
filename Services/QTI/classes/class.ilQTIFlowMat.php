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
* QTI flow_mat class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIFlowMat implements ilQTIMaterialAware
{
    public ?string $comment = null;
    /** @var ilQTIFlowMat[] */
    public array $flow_mat = [];
    /** @var ilQTIMaterial[] */
    public array $material = [];

    public function setComment(string $a_comment): void
    {
        $this->comment = $a_comment;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function addFlowMat(ilQTIFlowMat $a_flow_mat): void
    {
        $this->flow_mat[] = $a_flow_mat;
    }

    public function addMaterial(ilQTIMaterial $material): void
    {
        $this->material[] = $material;
    }

    public function getMaterial(int $index): ?ilQTIMaterial
    {
        return $this->material[$index] ?? null;
    }
}
