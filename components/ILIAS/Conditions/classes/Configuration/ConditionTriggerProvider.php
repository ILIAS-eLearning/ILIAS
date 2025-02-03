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

namespace ILIAS\Conditions\Configuration;

use ILIAS\StaticURL;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ilConditionHandler;
use ILIAS\UI\Factory as UIFactory;
use ilObject;
use ILIAS\Data\ReferenceId;
use ilLanguage;
use ilConditionHandlerGUI as ilConditionHandlerGUI;

class ConditionTriggerProvider
{
    private int $target_ref_id;
    private int $target_obj_id;
    private string $target_type;

    private array $data = [];

    private StaticURL\Services $static_url_service;
    private UIFactory $ui_factory;

    private ilLanguage $lng;


    public function __construct(int $target_ref_id, int $target_obj_id, string $target_type)
    {
        global $DIC;

        $this->static_url_service = $DIC['static_url'];
        $this->ui_factory = $DIC->ui()->factory();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('rbac');

        $this->target_ref_id = $target_ref_id;
        $this->target_obj_id = $target_obj_id;
        $this->target_type = $target_type;
        $this->read();
    }

    protected function read(): void
    {
        $conditions = ilConditionHandler::_getPersistedConditionsOfTarget(
            $this->target_ref_id,
            $this->target_obj_id,
            $this->target_type
        );

        foreach ($conditions as $condition) {
            $row = [];
            $row['id'] = $condition['id'];

            $link = $this->static_url_service->builder()->build(
                ilObject::_lookupType($condition['trigger_ref_id'], true),
                new ReferenceId($condition['trigger_ref_id'])
            );
            $row['trigger'] = $this->ui_factory->link()->standard(
                ilObject::_lookupTitle($condition['trigger_obj_id']),
                $link
            );
            $row['condition'] = ilConditionHandlerGUI::translateOperator(
                $condition['trigger_obj_id'],
                $condition['operator'],
                $condition['value']
            );

            if ($condition['obligatory']) {
                $row['obligatory'] = $this->ui_factory->symbol()->icon()->custom(
                    'assets/images/standard/icon_checked.svg',
                    '',
                    'small'
                );
            }
            $this->data[] = $row;
        }
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function sortData(Order $order): array
    {
        $data = $this->getData();
        [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
        usort($data, fn($a, $b) => $a[$order_field] <=> $b[$order_field]);
        if ($order_direction === 'DESC') {
            $data = array_reverse($data);
        }
        return $data;
    }

    public function limitData(Range $range, Order $order): array
    {
        return array_slice($this->sortData($order), $range->getStart(), $range->getLength());
    }

}
