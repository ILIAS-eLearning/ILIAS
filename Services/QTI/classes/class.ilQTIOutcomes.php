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
* QTI outcomes label class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIOutcomes
{
    public ?string $comment = null;
    /** @var (ilQTIDecvar)[] */
    public array $decvar = [];

    public function setComment(string $a_comment): void
    {
        $this->comment = $a_comment;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function addDecvar(ilQTIDecvar $a_decvar): void
    {
        $this->decvar[] = $a_decvar;
    }
}
