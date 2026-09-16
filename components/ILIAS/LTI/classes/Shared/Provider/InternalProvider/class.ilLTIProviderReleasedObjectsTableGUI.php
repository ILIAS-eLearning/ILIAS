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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Data\ReferenceId;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\StaticURL\Services;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Description of class ilLTIProviderReleasedObjectsTableGUI
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLTIProviderReleasedObjectsTableGUI implements DataRetrieval
{
    protected ilLanguage $lng;
    protected Factory $ui_factory;
    protected \ILIAS\UI\Renderer $ui_renderer;
    private ServerRequestInterface|RequestInterface $request;
    protected \ILIAS\Data\Factory $data_factory;
    protected ilCtrlInterface $ctrl;
    protected WrapperFactory $wrapper;
    protected \ILIAS\Refinery\Factory $refinery;
    private array $records;
    private string $id;

    public function __construct(string $a_id = '')
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->data_factory = new \ILIAS\Data\Factory();
        $this->ctrl = $DIC->ctrl();
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->id = $a_id ?: 'lti_released_objects';

        $this->records = $this->getRecords();
    }

    public function getColumns(): array
    {
        return [
            'type' => $this->ui_factory->table()->column()->statusIcon($this->lng->txt('type')),
            'title' => $this->ui_factory->table()->column()->link($this->lng->txt('title')),
            'consumer' => $this->ui_factory->table()->column()->text($this->lng->txt('lti_consumer'))
        ];
    }

    private function getRecords(): array
    {
        $rows = ilObjLTIAdministration::readReleaseObjects();

        $result = array();
        foreach ($rows as $row) {
            $ref_id = (int) $row['ref_id'];

            $type = ilObject::_lookupType(ilObject::_lookupObjId($ref_id));
            if ($type == 'rolf') {
                continue;
            }

            $result[] = array(
                'id' => $ref_id,
                'ref_id' => $ref_id,
                'obj_id' => ilObject::_lookupObjId($ref_id),
                'type' => $type,
                'title' => ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id)),
                'consumer' => $row['title']
            );
        }

        return $result;
    }

    /**
     * @throws ilCtrlException
     */
    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        global $DIC;

        /** @var Services $static_url */
        $static_url = $DIC["static_url"];

        $records = $this->applyOrdering($this->records, $order, $range);
        foreach ($records as $record) {
            $link = (string) $static_url->builder()->build(
                $record['type'],
                new ReferenceId($record['ref_id'])
            );

            $display_record = [
                'type' => $this->ui_factory->symbol()->icon()->standard($record['type'], $record['type'], Icon::SMALL),
                'title' => $this->ui_factory->link()->standard($record['title'], $link),
                'consumer' => $record['consumer']
            ];

            yield $row_builder->buildDataRow((string) $record['id'], $display_record);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->records);
    }

    /**
     */
    public function getHtml(): string
    {
        $table = $this->ui_factory->table()
            ->data($this, $this->lng->txt('lti_released_objects'), $this->getColumns())
            ->withOrder(new Order('title', Order::ASC))
            ->withRange(new Range(0, 20))
            ->withRequest($this->request);

        return $this->ui_renderer->render($table);
    }

    public function getId(): string
    {
        return $this->id;
    }

    protected function applyOrdering(array $records, Order $order, ?Range $range = null): array
    {
        [$order_field, $order_direction] = $order->join(
            [],
            fn($ret, $key, $value) => [$key, $value]
        );

        usort($records, static function (array $left, array $right) use ($order_field): int {
            $left_val = $left[$order_field] ?? '';
            $right_val = $right[$order_field] ?? '';
            return ilStr::strCmp($left_val, $right_val);
        });

        if ($order_direction === Order::DESC) {
            $records = array_reverse($records);
        }

        if ($range !== null) {
            $records = array_slice($records, $range->getStart(), $range->getLength());
        }

        return $records;
    }
}
