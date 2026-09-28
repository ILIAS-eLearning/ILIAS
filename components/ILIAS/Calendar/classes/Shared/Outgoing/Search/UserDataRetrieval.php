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

namespace ILIAS\Calendar\Shared\Outgoing\Search;

use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use Generator;
use ilObjUser;
use ilSearchResult;
use ilQueryParser;
use ilUtil;
use ilObjectSearchFactory;
use ILIAS\Calendar\Shared\DataRetrievalInterface;

class UserDataRetrieval implements DataRetrievalInterface
{
    protected ilSearchResult $search_result;

    public function __construct(
        protected string $query
    ) {
    }

    protected function getSearchResult(): ilSearchResult
    {
        if (isset($this->search_result)) {
            return $this->search_result;
        }

        $res_sum = new ilSearchResult();

        $query_parser = new ilQueryParser(ilUtil::stripSlashes($this->query));
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_OR);
        $query_parser->setMinWordLength(3);
        $query_parser->parse();

        $search = ilObjectSearchFactory::_getUserSearchInstance($query_parser);
        $search->enableActiveCheck(true);

        $search->setFields(array('login'));
        $res = $search->performSearch();
        $res_sum->mergeEntries($res);

        $search->setFields(array('firstname'));
        $res = $search->performSearch();
        $res_sum->mergeEntries($res);

        $search->setFields(array('lastname'));
        $res = $search->performSearch();
        $res_sum->mergeEntries($res);

        $res_sum->filter(ROOT_FOLDER_ID, false);

        return $this->search_result = $res_sum;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $data = [];
        foreach ($this->getSearchResult()->getResultIds() as $user_id) {
            $datum = [];
            $name = ilObjUser::_lookupName($user_id);
            $datum[UserTableBuilder::NAME_COLUMN] = $name['lastname'] . ', ' . $name['firstname'];
            $datum[UserTableBuilder::LOGIN_COLUMN] = $name['login'];
            $data[$user_id] = $datum;
        }

        $order_field = array_keys($order->get())[0];
        $order_direction = $order->get()[$order_field];
        uasort($data, fn($a, $b) => strtolower((string) $a[$order_field]) <=> strtolower((string) $b[$order_field]));
        if ($order_direction === Order::DESC) {
            $data = array_reverse($data, true);
        }
        $data = array_slice($data, $range->getStart(), $range->getLength(), true);

        foreach ($data as $user_id => $datum) {
            yield $row_builder->buildDataRow(
                (string) $user_id,
                $datum
            );
        }
    }

    /**
     * @return int[]
     */
    public function getAllIDs(): array
    {
        return $this->getSearchResult()->getResultIds();
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getSearchResult()->getResultIds());
    }
}
