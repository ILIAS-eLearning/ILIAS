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

/**
 * Filter field set
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerFilterSet
{
    /**
     * @var ilContainerFilterField[]
     */
    protected array $filters;
    /**
     * @var string[]
     */
    protected array $ids = [];

    /**
     * Constructor
     * @param ilContainerFilterField[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;

        $this->ids = array_map(static function (ilContainerFilterField $f): string {
            return $f->getRecordSetId() . "_" . $f->getFieldId();
        }, $filters);
    }

    /**
     * @return ilContainerFilterField[]
     */
    public function getFields(): array
    {
        return $this->filters;
    }

    /**
     * Has filter field
     * @param int $record_set_id
     * @param int $field_id
     * @return bool
     */
    public function has(int $record_set_id, int $field_id): bool
    {
        return in_array($record_set_id . "_" . $field_id, $this->ids, true);
    }
}
