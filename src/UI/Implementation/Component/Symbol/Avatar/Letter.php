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
 *********************************************************************/
 
namespace ILIAS\UI\Implementation\Component\Symbol\Avatar;

use ILIAS\UI\Component as C;

class Letter extends Avatar implements C\Symbol\Avatar\Letter
{
    public function getAbbreviation() : string
    {
        return (substr($this->getUsername(), 0, 2));
    }

    public function getBackgroundColorVariant() : int
    {
        return ((crc32($this->getAbbreviation()) % 26) + 1);
    }
}
