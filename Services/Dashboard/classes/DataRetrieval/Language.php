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

namespace ILIAS\Dashboard\DataRetrieval;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ilLanguage;
use ilObjLanguage;

class Language implements DataRetrieval
{
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
    }
    public function getRows(DataRowBuilder $row_builder, array $visible_column_ids, Range $range, Order $order, ?array $filter_data, ?array $additional_parameters): Generator
    {
        foreach ($this->lng->getInstalledLanguages() as $key) {
            $title = $this->lng->txt('meta_l_' . $key);
            if ($key === $this->lng->getDefaultLanguage()) {
                $title .= ' (' . $this->lng->txt('system_language') . ')';
            }
            yield $row_builder->buildDataRow($key, [
                'name' => $title,
                'user_count' => ilObjLanguage::countUsers($key)
            ]);
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->lng->getInstalledLanguages());
    }
}
