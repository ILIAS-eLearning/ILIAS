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
 *********************************************************************/

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;

class RowFactory implements T\RowFactory
{
    /**
     * @var array <id, Transformation>
     */
    protected array $cell_transformations;

    /**
     * @param \ILIAS\UI\Implementation\Component\Table\Transformation[] $cell_transformations
     */
    public function __construct(array $cell_transformations)
    {
        $this->cell_transformations = $cell_transformations;
    }

    public function map(array $record): array
    {
        $row = [];
        foreach (array_keys($this->cell_transformations) as $id) {
            $row[$id] = '';
            if (array_key_exists($id, $record)) {
                foreach ($this->cell_transformations[$id] as $trafo) {
                    $row[$id] = $trafo($record[$id]);
                }
            }
        }
        return $row;
    }
}
