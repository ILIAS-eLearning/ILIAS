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
use ilBadge;

class ilBadgeImageTemplateTableGUI
{
    private readonly Factory $factory;
    private readonly Renderer $renderer;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly ServerRequestInterface|RequestInterface $request;
    private readonly Services $http;
    private readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $tpl;
    public function __construct() {
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
     * @param Factory  $f
     * @param Renderer $r
     * @return DataRetrieval|__anonymous@1221
     */
    protected function buildDataRetrievalObject(Factory $f, Renderer $r)
    {
        return new class ($f, $r) implements DataRetrieval {
            public function __construct(
                protected Factory $ui_factory,
                protected Renderer $ui_renderer
            ) {
            }

            /**
             * @param Container $DIC
             * @param array $data
             * @return array
             */
            protected function getBadgeImageTemplates(Container $DIC, array $data) : array
            {
                $modal_container = new ModalBuilder();

                foreach (ilBadgeImageTemplate::getInstances() as $template) {
                    $image_html = '';
                    if ($template->getId() !== null) {
                        $badge_template_image = $template->getImageFromResourceId($template->getImageRid());
                        $badge_template_image_large = $template->getImageFromResourceId($template->getImageRid(), null, 0);
                        if($badge_template_image !== '') {
                            $badge_img = $DIC->ui()->factory()->image()->responsive(
                                $badge_template_image,
                                $template->getTitle()
                            );
                            $image_html = $DIC->ui()->renderer()->render($badge_img);
                        }

                        if($badge_template_image_large !== '') {
                            $badge_img_large = $DIC->ui()->factory()->image()->responsive(
                                $badge_template_image_large,
                                $template->getTitle()
                            );

                            $modal = $modal_container->constructModal($badge_img_large, $template->getTitle(), '');
                            $data[] =
                                ['id' => $template->getId(),
                                 'image_rid' => $modal_container->renderShyButton($image_html, $modal) . ' ' .   $modal_container->renderModal($modal),
                                 'title' =>   $modal_container->renderShyButton($template->getTitle(), $modal),
                                ];
                        }

                    }
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
                $data = array();

                $data = $this->getBadgeImageTemplates($DIC, $data);

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
            'badge_image_template_edit' => $f->table()->action()->single(
                $this->lng->txt("edit"),
                $url_builder->withParameter($action_parameter_token, "badge_image_template_editImageTemplate"),
                $row_id_token
            ),
            'badge_image_template_delete' =>
                $f->table()->action()->standard(
                    $this->lng->txt("delete"),
                    $url_builder->withParameter($action_parameter_token, "badge_image_template_delete"),
                    $row_id_token
                )
        ];
    }

    public function renderTable() : void
    {
        $f = $this->factory;
        $r = $this->renderer;
        $request = $this->request;
        $df = new \ILIAS\Data\Factory();

        $columns = [
            'image_rid' => $f->table()->column()->text($this->lng->txt("image")),
            'title' => $f->table()->column()->text($this->lng->txt("title")),
        ];

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

        $data_retrieval = $this->buildDataRetrievalObject($f, $r);

        $table = $f->table()
                   ->data('', $columns, $data_retrieval)
                   ->withActions($actions)
                   ->withRequest($request);

        $out = [$table];
        $query = $this->http->wrapper()->query();
        if ($query->has('tid_id')) {
            $query_values = $query->retrieve('tid_id', $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()));

            $items = [];
            if ($query_values === ['ALL_OBJECTS']) {
                foreach (ilBadgeImageTemplate::getInstances() as $template) {
                    if ($template->getId() !== null) {
                        $items[] = $f->modal()->interruptiveItem()->keyValue($template->getId(), $template->getId(),
                            $template->getTitle());
                    }
                }
            } else {
                if (is_array($query_values)) {
                    foreach ($query_values as $id) {
                        $badge = new ilBadgeImageTemplate($id);
                        $items[] = $f->modal()->interruptiveItem()->keyValue($id, $badge->getId(), $badge->getTitle());
                    }
                } else {
                    $badge = new ilBadgeImageTemplate($query_values);
                    $items[] = $f->modal()->interruptiveItem()->keyValue($badge->getId(), $badge->getId(),
                        $badge->getTitle());
                }

            }
            $action = $query->retrieve($action_parameter_token->getName(), $this->refinery->to()->string());
            if ($action === 'badge_image_template_delete') {
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
