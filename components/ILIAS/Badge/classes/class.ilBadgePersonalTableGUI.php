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

use ILIAS\UI\Factory;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Services;
use Psr\Http\Message\RequestInterface;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\URI;
use ILIAS\UI\Implementation\Component\Link\Standard;
use ILIAS\Badge\ilBadgeImage;
use ILIAS\Badge\PresentationHeader;
use ILIAS\UI\Implementation\Component\Image\Image;
use ILIAS\Badge\ModalBuilder;

/*
 * @ilCtrl_IsCalledBy ilObjBadgeAdministration: ilObjectBadgeTable
 */

class ilBadgePersonalTableGUI
{
    private readonly Factory $factory;
    private readonly Renderer $renderer;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly ServerRequestInterface|RequestInterface $request;
    private readonly Services $http;
    private readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $tpl;
    public function __construct()
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

    protected function buildDataRetrievalObject(Factory $f, Renderer $r): DataRetrieval
    {
        return new class ($f, $r) implements DataRetrieval {
            private readonly ilBadgeImage $badge_image_service;
            private readonly Factory $factory;
            private readonly Renderer $renderer;
            private readonly ilObjUser $user;
            private readonly ilAccess $access;

            private readonly ilLanguage $lng;

            public function __construct(
                protected Factory $ui_factory,
                protected Renderer $ui_renderer
            ) {
                global $DIC;
                $this->badge_image_service = new ilBadgeImage($DIC->resourceStorage(), $DIC->upload(), $DIC->ui()->mainTemplate());
                $this->factory = $this->ui_factory;
                $this->renderer = $this->ui_renderer;
                $this->user = $DIC->user();
                $this->access = $DIC->access();
                $this->lng = $DIC->language();
            }


            public function getRows(
                DataRowBuilder $row_builder,
                array $visible_column_ids,
                Range $range,
                Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): Generator {
                $records = $this->getRecords($range, $order);
                foreach ($records as $idx => $record) {
                    $row_id = (string) $record['id'];
                    yield $row_builder->buildDataRow($row_id, $record);
                }
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return count($this->getRecords());
            }

            protected function getRecords(Range $range = null, Order $order = null): array
            {
                $data = [];
                $a_user_id = $this->user->getId();
                $badge_img_large = new Image(Image::STANDARD, '', '');

                foreach (ilBadgeAssignment::getInstancesByUserId($a_user_id) as $ass) {
                    $image_html = '';
                    $badge = new ilBadge($ass->getBadgeId());
                    $modal_container = new ModalBuilder($ass);
                    $image_rid = $this->badge_image_service->getImageFromBadge($badge);
                    if ($image_rid != '') {
                        $badge_img = $this->factory->image()->responsive($image_rid, $badge->getTitle());
                        $image_html = $this->renderer->render($badge_img);

                        $image_html_large = $this->badge_image_service->getImageFromResourceId($badge, $badge->getImageRid(), 0);
                        if ($image_html_large !== '') {
                            $badge_img_large = $this->ui_factory->image()->responsive(
                                $image_html_large,
                                $badge->getTitle()
                            );
                        }
                    }
                    $parent = null;
                    if ($badge->getParentId()) {
                        $parent = $badge->getParentMeta();
                        if ($parent["type"] === "bdga") {
                            $parent = null;
                        }
                    }
                    $awarded_by = '';
                    if ($parent !== null) {
                        $ref_ids = ilObject::_getAllReferences($parent['id']);
                        reset($ref_ids);
                        $ref_id = current($ref_ids);

                        if ($this->access->checkAccess('read', '', $ref_id)) {
                            $container_url = ilLink::_getLink($ref_id);
                            $awarded_by = $this->renderer->render(new Standard($parent['title'], new URI($container_url)));
                        }
                    }

                    $information = [
                        'awarded_by' => $awarded_by
                    ];

                    $modal = $modal_container->constructModal($badge_img_large, $badge->getTitle(), '', $information);

                    $data[] = [
                        'id' => $badge->getId(),
                        'image' => $this->ui_renderer->render($this->ui_factory->button()->shy($image_html, $modal->getShowSignal())) . ' ' . $this->ui_renderer->render($modal),
                        'title' => $this->ui_renderer->render($this->ui_factory->button()->shy($badge->getTitle(), $modal->getShowSignal())),
                        'user' => '',
                        'badge_in_profile' => '',
                        "badge_issued_on" => (new \DateTimeImmutable())->setTimestamp($ass->getTimestamp()),
                        "awarded_by" => $awarded_by,
                        "parent" => $parent,
                        "active" => (bool) $ass->getPosition()
                    ];
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
        URLBuilderToken $row_id_token
    ): array {
        $f = $this->factory;

        return [
            'obj_badge_activate' => $f->table()->action()->multi( //never in multi actions
                $this->lng->txt("activate"),
                $url_builder->withParameter($action_parameter_token, "obj_badge_activate"),
                $row_id_token
            ),
            'obj_badge_deactivate' =>
                $f->table()->action()->multi( //in both
                    $this->lng->txt("deactivate"),
                    $url_builder->withParameter($action_parameter_token, "obj_badge_deactivate"),
                    $row_id_token
                )
        ];
    }

    public function renderTable(): void
    {
        $f = $this->factory;
        $r = $this->renderer;
        $refinery = $this->refinery;
        $request = $this->request;
        $df = new \ILIAS\Data\Factory();
        $data_format = $df->dateFormat();
        $date_format = $data_format->withTime24($df->dateFormat()->germanShort());

        $columns = [
            'image' => $f->table()->column()->text($this->lng->txt("image")),
            'title' => $f->table()->column()->text($this->lng->txt("title")),
            'awarded_by' => $f->table()->column()->text($this->lng->txt("awarded_by")),
            'badge_issued_on' => $f->table()->column()->date($this->lng->txt("badge_issued_on"), $date_format),
            'active' => $f->table()->column()->boolean($this->lng->txt("badge_in_profile"), $this->lng->txt("yes"), $this->lng->txt("no")),
        ];

        $table_uri = $df->uri($request->getUri()->__toString());
        $url_builder = new URLBuilder($table_uri);
        $query_params_namespace = ['badge'];

        list($url_builder, $action_parameter_token, $row_id_token) =
            $url_builder->acquireParameters(
                $query_params_namespace,
                "table_action",
                "id"
            );

        $data_retrieval = $this->buildDataRetrievalObject($f, $r);

        $actions = $this->getActions($url_builder, $action_parameter_token, $row_id_token);

        $table = $f->table()
                   ->data('', $columns, $data_retrieval)
                   ->withActions($actions)
                   ->withRequest($request);

        global $DIC;
        $pres = new PresentationHeader($DIC, ilBadgeProfileGUI::class);
        $pres->show($this->lng->txt('table_view'));
        $out = [$table];
        $this->tpl->setContent($r->render($out));
    }

}
