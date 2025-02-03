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

namespace ILIAS\Badge;

use ILIAS\UI\Factory;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ilBadgeImageTemplate;
use ilLanguage;
use ilGlobalTemplateInterface;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Services;
use Psr\Http\Message\RequestInterface;
use ILIAS\UI\Component\Table\DataRowBuilder;
use Generator;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\UI\Component\Table\Column\Column;

class ilBadgeImageTemplateTableGUI implements DataRetrieval
{
    private readonly Factory $factory;
    private readonly Renderer $renderer;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly ServerRequestInterface|RequestInterface $request;
    private readonly Services $http;
    private readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $tpl;
    /** @var list<array{id: int, image: string, title: string, title_sortable: string}>|null */
    private ?array $cached_records = null;

    public function __construct(protected bool $has_write = false)
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        $this->request = $DIC->http()->request();
        $this->http = $DIC->http();
    }

    /**
     * @return list<array{id: int, image: string, title: string, title_sortable: string}>
     */
    private function getRecords(): array
    {
        if ($this->cached_records !== null) {
            return $this->cached_records;
        }

        $modal_container = new ModalBuilder();
        $rows = [];

        foreach (ilBadgeImageTemplate::getInstances() as $template) {
            $image = '';
            $title = $template->getTitle();

            $image_src = $template->getImageFromResourceId();
            if ($image_src !== '') {
                $image_component = $this->factory->image()->responsive(
                    $image_src,
                    $template->getTitle()
                );
                $image_html = $this->renderer->render($image_component);

                $image_src_large = $template->getImageFromResourceId(
                    ilBadgeImage::IMAGE_SIZE_XL
                );
                $large_image_component = $this->factory->image()->responsive(
                    $image_src_large,
                    $template->getTitle()
                );

                $modal = $modal_container->constructModal($large_image_component, $template->getTitle());

                $image = implode('', [
                    $modal_container->renderShyButton($image_html, $modal),
                    $modal_container->renderModal($modal)
                ]);
                $title = $modal_container->renderShyButton($template->getTitle(), $modal);
            }

            $rows[] = [
                'id' => $template->getId(),
                'image' => $image,
                'title' => $title,
                'title_sortable' => $template->getTitle()
            ];
        }

        $this->cached_records = $rows;

        return $rows;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        $records = $this->getRecords();

        if ($order) {
            [$order_field, $order_direction] = $order->join(
                [],
                fn($ret, $key, $value) => [$key, $value]
            );

            usort($records, static function (array $left, array $right) use ($order_field): int {
                if ($order_field === 'title') {
                    return \ilStr::strCmp(
                        $left[$order_field . '_sortable'],
                        $right[$order_field . '_sortable']
                    );
                }

                return $left[$order_field] <=> $right[$order_field];
            });

            if ($order_direction === Order::DESC) {
                $records = array_reverse($records);
            }
        }

        if ($range) {
            $records = \array_slice($records, $range->getStart(), $range->getLength());
        }

        foreach ($records as $record) {
            yield $row_builder->buildDataRow((string) $record['id'], $record);
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return \count($this->getRecords());
    }

    /**
     * @return array<string, Column>
     */
    public function getColumns(): array
    {
        return [
            'image' => $this->factory->table()->column()->text($this->lng->txt('image'))->withIsSortable(false),
            'title' => $this->factory->table()->column()->text($this->lng->txt('title'))
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    private function getActions(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken $row_id_token
    ): array {
        return $this->has_write ? [
            'badge_image_template_edit' => $this->factory->table()->action()->single(
                $this->lng->txt('edit'),
                $url_builder->withParameter($action_parameter_token, 'badge_image_template_editImageTemplate'),
                $row_id_token
            ),
            'badge_image_template_delete' =>
                $this->factory->table()->action()->standard(
                    $this->lng->txt('delete'),
                    $url_builder->withParameter($action_parameter_token, 'badge_image_template_delete'),
                    $row_id_token
                )
        ] : [];
    }

    public function renderTable(): void
    {
        $df = new \ILIAS\Data\Factory();

        $table_uri = $df->uri($this->request->getUri()->__toString());
        $url_builder = new URLBuilder($table_uri);
        $query_params_namespace = ['tid'];

        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            $query_params_namespace,
            'table_action',
            'id',
        );

        $table = $this->factory
            ->table()
            ->data($this->lng->txt('badge_image_templates'), $this->getColumns(), $this)
            ->withId(self::class)
            ->withOrder(new Order('title', Order::ASC))
            ->withActions($this->getActions($url_builder, $action_parameter_token, $row_id_token))
            ->withRequest($this->request);

        $out = [$table];
        $query = $this->http->wrapper()->query();
        if ($query->has('tid')) {
            $query_values = $query->retrieve(
                'tid',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );

            $items = [];
            if ($query_values === ['ALL_OBJECTS']) {
                foreach (ilBadgeImageTemplate::getInstances() as $template) {
                    if ($template->getId() !== null) {
                        $items[] = $this->factory->modal()->interruptiveItem()->keyValue(
                            (string) $template->getId(),
                            (string) $template->getId(),
                            $template->getTitle()
                        );
                    }
                }
            } elseif (\is_array($query_values)) {
                foreach ($query_values as $id) {
                    $badge = new ilBadgeImageTemplate((int) $id);
                    $items[] = $this->factory->modal()->interruptiveItem()->keyValue(
                        (string) $id,
                        (string) $badge->getId(),
                        $badge->getTitle()
                    );
                }
            } else {
                $badge = new ilBadgeImageTemplate($query_values);
                $items[] = $this->factory->modal()->interruptiveItem()->keyValue(
                    (string) $badge->getId(),
                    (string) $badge->getId(),
                    $badge->getTitle()
                );
            }
            if ($query->has($action_parameter_token->getName())) {
                $action = $query->retrieve($action_parameter_token->getName(), $this->refinery->kindlyTo()->string());
                if ($action === 'badge_image_template_delete') {
                    $this->http->saveResponse(
                        $this->http
                            ->response()
                            ->withBody(
                                Streams::ofString($this->renderer->renderAsync([
                                    $this->factory->modal()->interruptive(
                                        $this->lng->txt('badge_deletion'),
                                        $this->lng->txt('badge_deletion_confirmation'),
                                        '#'
                                    )->withAffectedItems($items)
                                ]))
                            )
                    );
                    $this->http->sendResponse();
                    $this->http->close();
                }
            }
        }

        $this->tpl->setContent($this->renderer->render($out));
    }
}
