<?php
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
use ILIAS\DI\Container;
use ilBadgeHandler;
use ilBadge;
use ilBadgeAuto;
use ILIAS\UI\Implementation\Component\Table\Column\Boolean;
use ilBadgeRenderer;

class ilBadgeTableGUI
{
    private readonly Factory $factory;
    private readonly Renderer $renderer;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly ServerRequestInterface|RequestInterface $request;
    private readonly Services $http;
    private readonly int $parent_id;
    private readonly string $parent_type;
    private readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $tpl;
    public function __construct(int $parent_obj_id, string $parent_obj_type) {
        global $DIC;
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        $this->request = $DIC->http()->request();
        $this->http = $DIC->http();

        $this->parent_id = $parent_obj_id;
        $this->parent_type = $parent_obj_type;
    }
    protected function buildColumns() : array
    {
        $column = $this->factory->table()->column();
        $lng = $this->lng;

        return [
            'image_rid' => $column->text($lng->txt("image")),
            'title' => $column->text($lng->txt("title")),
            'type' => $column->text($lng->txt("type")),
            'active' => $column->boolean($lng->txt("active"), $lng->txt("yes"), $lng->txt("no")),
        ];
    }

    /**
     * @param Factory  $f
     * @param Renderer $r
     * @param int      $p
     * @param string   $type
     * @return DataRetrieval@2044
     */
    protected function buildDataRetrievalObject(Factory $f, Renderer $r, int $p, string $type)
    {
        return new class ($f, $r, $p, $type) implements DataRetrieval {
            protected int $parent_obj_id;
            protected string $parent_obj_type;
            protected ilBadgeImage $badge_image_service;
            protected Factory $factory;
            protected Renderer $renderer;
            public function __construct(
                protected Factory $ui_factory,
                protected Renderer $ui_renderer,
                protected int $parent_id,
                protected string $parent_type
            ) {
                global $DIC;
                $this->parent_obj_id = $parent_id;
                $this->parent_obj_type = $parent_type;
                $this->badge_image_service = new ilBadgeImage($DIC->resourceStorage(), $DIC->upload(), $DIC->ui()->mainTemplate());
                $this->factory = $this->ui_factory;
                $this->renderer = $this->ui_renderer;
            }

            /**
             * @param Container $DIC
             * @param array $data
             * @return array
             */
            protected function getBadges(Container $DIC) : array
            {
                $data = [];
                $badge_img_large = null;
                $modal_container = new ModalBuilder();

                foreach (ilBadge::getInstancesByParentId($this->parent_id) as $badge) {
                    $title = $badge->getTitle();
                    $image_html = '';
                    $badge_rid = $badge->getImageRid();
                    $image_src = $this->badge_image_service->getImageFromResourceId($badge, $badge_rid);
                    $badge_image_large = $this->badge_image_service->getImageFromResourceId($badge, $badge_rid, 0);
                    if($badge_rid != '') {
                        $badge_template_image = $image_src;
                        if($badge_template_image !== '') {
                            $badge_img = $this->factory->image()->responsive(
                                $badge_template_image,
                                $badge->getTitle()
                            );
                            $image_html = $this->renderer->render($badge_img);
                        }
                    }

                    if($badge_img_large !== '') {
                        $badge_img_large = $DIC->ui()->factory()->image()->responsive(
                            $badge_image_large,
                            $badge->getTitle()
                        );
                        $badge_information = [
                            'description' => $badge->getDescription(),
                            'badge_criteria' => $badge->getCriteria(),
                        ];

                        $modal = $modal_container->constructModal($badge_img_large, $badge->getTitle(), '',
                            $badge_information);
                    }
                    $data[] = array(
                        'id' => $badge->getId(),
                        'badge' => $badge,
                        'active' => $badge->isActive(),
                        'type' => ($this->parent_type !== 'bdga')
                            ? ilBadge::getExtendedTypeCaption($badge->getTypeInstance())
                            : $badge->getTypeInstance()->getCaption(),
                        'manual' => (!$badge->getTypeInstance() instanceof ilBadgeAuto),
                        'image_rid' => $modal_container->renderShyButton($image_html, $modal) . ' ' .   $modal_container->renderModal($modal),
                        'title' =>   $modal_container->renderShyButton($title, $modal),
                        'renderer' => ''
                    );
                }
                return $data;
            }

            public function getRows(
                DataRowBuilder $row_builder,
                array $visible_column_ids,
                Range $range,
                Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ) : Generator {
                $records = $this->getRecords($range, $order);
                foreach ($records as $idx => $record) {
                    $row_id = (string) $record['id'];
                    yield $row_builder->buildDataRow($row_id, $record);
                }
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ) : ?int {
                return count($this->getRecords());
            }

            protected function getRecords(Range $range = null, Order $order = null) : array
            {
                global $DIC;
                $data = $this->getBadges($DIC);

                if ($order) {
                    list($order_field, $order_direction) = $order->join([],
                        fn($ret, $key, $value) => [$key, $value]);
                    usort($data, fn($a, $b) => $a[$order_field] <=> $b[$order_field]);
                    if ($order_direction === 'DESC') {
                        $data = array_reverse($data);
                    }
                }
                if ($range) {
                    $data = array_slice($data, $range->getStart(), $range->getLength());
                }

                return $data;
            }
        };
    }

    /**
     * @param URLBuilder      $url_builder
     * @param URLBuilderToken $action_parameter_token
     * @param URLBuilderToken $row_id_token
     * @return array
     */
    protected function getActions(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken  $row_id_token
    ) : array {
        $f = $this->factory;
        return [
            'badge_table_activate' =>
                $f->table()->action()->multi(
                    $this->lng->txt("activate"),
                    $url_builder->withParameter($action_parameter_token, "badge_table_activate"),
                    $row_id_token
                ),
            'badge_table_deactivate' =>
                $f->table()->action()->multi(
                    $this->lng->txt("deactivate"),
                    $url_builder->withParameter($action_parameter_token, "badge_table_deactivate"),
                    $row_id_token
                ),
            'badge_table_edit' => $f->table()->action()->single(
                $this->lng->txt("edit"),
                $url_builder->withParameter($action_parameter_token, "badge_table_edit"),
                $row_id_token
            ),
            'badge_table_delete' =>
                $f->table()->action()->standard(
                    $this->lng->txt("delete"),
                    $url_builder->withParameter($action_parameter_token, "badge_table_delete"),
                    $row_id_token
                )
        ];
    }

    public function renderTable() : void
    {
        $f = $this->factory;
        $r = $this->renderer;
        $df = new \ILIAS\Data\Factory();

        $refinery = $this->refinery;
        $request = $this->request;

        $columns = $this->buildColumns();
        $table_uri = $df->uri($request->getUri()->__toString());
        $url_builder = new URLBuilder($table_uri);
        $query_params_namespace = ['tid'];

        list($url_builder, $action_parameter_token, $row_id_token) =
            $url_builder->acquireParameters(
                $query_params_namespace,
                "table_action",
                "id"
            );

        $actions = $this->getActions($url_builder, $action_parameter_token, $row_id_token);
        $data_retrieval = $this->buildDataRetrievalObject($f, $r, $this->parent_id, $this->parent_type);
        $table = $f->table()->data('', $columns, $data_retrieval)
                   ->withActions($actions)
                   ->withRequest($request);
        $out = [$table];

        $query = $this->http->wrapper()->query();

        if ($query->has($action_parameter_token->getName())) {
            $action = $query->retrieve($action_parameter_token->getName(), $refinery->to()->string());
            $ids = $query->retrieve($row_id_token->getName(), $refinery->custom()->transformation(fn($v) => $v));
            $listing = $f->listing()->characteristicValue()->text([
                'table_action' => $action,
                'id' => print_r($ids, true),
            ]);

            if ($action === 'delete') {
                $items = [];
                foreach ($ids as $id) {
                    $items[] = $f->modal()->interruptiveItem()->keyValue($id, $row_id_token->getName(), $id);
                }
                echo($r->renderAsync([
                    $f->modal()->interruptive(
                        $this->lng->txt('badge_deletion'),
                        $this->lng->txt('badge_deletion_confirmation'),
                        '#'
                    )->withAffectedItems($items)
                      ->withAdditionalOnLoadCode(static fn($id) : string => "console.log('ASYNC JS');")
                ]));
                exit();
            }
        }

        $this->tpl->setContent($r->render($out));
    }
}
