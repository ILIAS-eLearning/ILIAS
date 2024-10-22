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
use ilBadgeAuto;
use ilObject;
use ilBadge;
use ilBadgeAssignment;
use ilUserQuery;
use DateTimeImmutable;

class ilBadgeUserTableGUI
{
    private readonly Factory $factory;
    private readonly Renderer $renderer;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly ServerRequestInterface|RequestInterface $request;
    private readonly Services $http;
    private readonly int $parent_ref_id;
    private readonly ?ilBadge $award_badge;
    private readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $tpl;
    public function __construct(int $parent_ref_id, ?ilBadge $award_badge = null) {
        global $DIC;
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        $this->request = $DIC->http()->request();
        $this->http = $DIC->http();
        $this->parent_ref_id = $parent_ref_id;
        $this->award_badge = $award_badge;
    }

    /**
     * @param Factory  $f
     * @param Renderer $r
     * @return DataRetrieval|__anonymous@1221
     */
    protected function buildDataRetrievalObject(Factory $f, Renderer $r, int $parent_ref_id, ?ilBadge $award_badge = null)
    {
        return new class ($f, $r, $parent_ref_id, $award_badge) implements DataRetrieval {
            public function __construct(
                protected Factory $ui_factory,
                protected Renderer $ui_renderer,
                protected int $parent_ref_id,
                protected ?ilBadge $award_badge = null
            ) {
            }

            /**
             * @param Container $DIC
             * @param array $data
             * @return array
             */
            protected function getBadgeImageTemplates(Container $DIC, array $data) : array
            {

                global $DIC;
                $a_parent_obj_id     = null;
                $assignments         = null;
                $user_ids            = null;
                $parent_ref_id       = $this->parent_ref_id;
                $a_restrict_badge_id = 0;
                $data                = [];
                $badges              = [];
                $tree                = $DIC->repositoryTree();

                if (!$a_parent_obj_id) {
                    $a_parent_obj_id = ilObject::_lookupObjId($parent_ref_id);
                }

                if ($parent_ref_id) {
                    $user_ids = ilBadgeHandler::getInstance()->getUserIds($parent_ref_id, $a_parent_obj_id);
                }

                $obj_ids = [$a_parent_obj_id];

                foreach ($tree->getSubTree($tree->getNodeData($parent_ref_id)) as $node) {
                    $obj_ids[] = $node["obj_id"];
                }

                foreach ($obj_ids as $obj_id) {
                    foreach (ilBadge::getInstancesByParentId($obj_id) as $badge) {
                        $badges[$badge->getId()] = $badge;
                    }

                    foreach (ilBadgeAssignment::getInstancesByParentId($obj_id) as $ass) {
                        if ($a_restrict_badge_id &&
                            $a_restrict_badge_id !== $ass->getBadgeId()) {
                            continue;
                        }

                        $assignments[$ass->getUserId()][] = $ass;
                    }
                }

                if (!$user_ids && $assignments !== null) {
                    $user_ids = array_keys($assignments);
                }

                $tmp['set'] = [];
                if (count($user_ids) > 0) {
                    $uquery = new ilUserQuery();
                    $uquery->setLimit(9999);
                    $uquery->setUserFilter($user_ids);
                    $tmp = $uquery->query();
                }
                foreach ($tmp["set"] as $user) {
                    if (is_array($assignments) && array_key_exists($user["usr_id"], $assignments)) {
                        foreach ($assignments[$user["usr_id"]] as $user_ass) {
                            $idx = $user_ass->getBadgeId() . "-" . $user["usr_id"];
                            $badge = $badges[$user_ass->getBadgeId()];
                            $parent = $badge->getParentMeta();
                            $timestamp = $user_ass->getTimestamp();
                            $immutable = new DateTimeImmutable();
                            $user_id = $user['usr_id'];
                            $name = $user['lastname'] . ', ' . $user['firstname'];
                            $login = $user['login'];
                            $type = ilBadge::getExtendedTypeCaption($badge->getTypeInstance());
                            $title = $badge->getTitle();
                            $issued = $immutable->setTimestamp($timestamp);
                            $parent_id =  $parent["id"] ?? 0;
                            $data[$idx] = [
                                "user_id" => $user_id,
                                "name" => $name,
                                "login" => $login,
                                "type" => $type,
                                "title" => $title,
                                "issued" => $issued,
                                "parent_id" => $parent_id,
                                "parent_meta" => $parent
                            ];
                        }
                    }
                    elseif ($this->award_badge) {
                        $idx = "0-" . $user["usr_id"];
                        $user_id = $user['usr_id'];
                        $name = $user['lastname'] . ', ' . $user['firstname'];
                        $login = $user['login'];
                        $data[$idx] = [
                            "user_id" => $user_id,
                            "name" => $name,
                            "login" => $login,
                            "type" => "",
                            "title" => "",
                            "issued" => "",
                            "parent_id" => ""
                        ];
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
                    if(isset($idx)) {
                        $row_id = (string) $idx;
                        yield $row_builder->buildDataRow($row_id, $record);
                    }
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

    public function renderTable() : void
    {
        $f = $this->factory;
        $r = $this->renderer;
        $refinery = $this->refinery;
        $request = $this->request;
        $df = new \ILIAS\Data\Factory();

        $columns = [
            'name' => $f->table()->column()->text($this->lng->txt("name")),
            'login' => $f->table()->column()->text($this->lng->txt("login")),
            'type' => $f->table()->column()->text($this->lng->txt("type")),
            'title' => $f->table()->column()->text($this->lng->txt("title")),
            'issued' => $f->table()->column()->date($this->lng->txt("badge_issued_on"), $df->dateFormat()->germanShort())
        ];

        $table_uri = $df->uri($request->getUri()->__toString());
        $url_builder = new URLBuilder($table_uri);
        $query_params_namespace = ['tid'];

        list($url_builder, $action_parameter_token, $row_id_token) =
            $url_builder->acquireParameters(
                $query_params_namespace,
                "table_action",
                "id",
            );


        $data_retrieval = $this->buildDataRetrievalObject($f, $r, $this->parent_ref_id, $this->award_badge);

        $table = $f->table()
                   ->data('', $columns, $data_retrieval)
                   ->withRequest($request);

        $out = [$table];
        $this->tpl->setContent($r->render($out));
    }
}
