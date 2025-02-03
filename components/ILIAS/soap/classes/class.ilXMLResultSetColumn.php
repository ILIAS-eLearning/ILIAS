<?php

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

declare(strict_types=1);
/**
 * Column Class for XMLResultSet
 * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 */
class ilXMLResultSetColumn
{
    private string $name;
    private int $index;

    public function __construct(int $index, string $name)
    {
        $this->name = $name;
        $this->index = $index;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIndex(): int
    {
        return $this->index;
    }
}
