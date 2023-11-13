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

use ILIAS\Data\URI;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\Component\Table\Data;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\DATA\Factory as DataFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\URLBuilderToken;

/**
 * Description of ilDidacticTemplateSettingsTableGUI
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplates
 */
class ilDidacticTemplateSettingsTableGUI
{
    protected ilAccessHandler $access;
    protected UIRenderer $renderer;
    protected UIFactory $ui_factory;
    protected DataFactory $data_factory;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected HTTPServices $http;
    protected ilDidacticTemplateSettingsGUI $didactic_template_settings_gui;
    protected RefineryFactory $refinery;
    protected int $ref_id;

    public function __construct(
        ilDidacticTemplateSettingsGUI $didactic_template_settings_gui,
        int $ref_id
    ) {
        global $DIC;
        $this->ref_id = $ref_id;
        $this->renderer = $DIC->ui()->renderer();
        $this->ui_factory = $DIC->ui()->factory();
        $this->didactic_template_settings_gui = $didactic_template_settings_gui;
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->data_factory = new DataFactory();
    }

    protected function createTable(
        ilDidacticTemplateSettingsTableDataRetrieval $data_retrieval
    ): Data {
        $this->lng->loadLanguageModule('search');
        $this->lng->loadLanguageModule('meta');

        $columns = [
            'icon' => $this->ui_factory->table()->column()->statusIcon(($this->lng->txt('icon')))
                ->withIsSortable(false)
                ->withIsOptional(true),
            'title' => $this->ui_factory->table()->column()->text($this->lng->txt('search_title_description')),
            'applicable' => $this->ui_factory->table()->column()->text($this->lng->txt('didactic_applicable_for')),
            'scope' => $this->ui_factory->table()->column()->text($this->lng->txt('didactic_scope')),
            'enabled' => $this->ui_factory->table()->column()->statusIcon($this->lng->txt('active'))
        ];

        /**
         * @var URLBuilder $url_builder
         * @var URLBuilderToken $action_parameter_token
         * @var URLBuilderToken $row_id_token
         */
        $query_params_namespace = ['didactic_template'];
        $url_builder = new URLBuilder(
            new URI(
                ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                    ilDidacticTemplateSettingsGUI::class,
                    'handleTableActions'
                )
            )
        );
        list($url_builder, $action_parameter_token, $row_id_token) = $url_builder->acquireParameters(
            $query_params_namespace,
            'table_action',
            'template_ids'
        );

        $actions = [];
        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            $actions = [
                'settings' => $this->ui_factory->table()->action()->single(
                    $this->lng->txt('settings'),
                    $url_builder->withParameter($action_parameter_token, 'editTemplate'),
                    $row_id_token
                ),
                'copy' => $this->ui_factory->table()->action()->single(
                    $this->lng->txt('copy'),
                    $url_builder->withParameter($action_parameter_token, 'copyTemplate'),
                    $row_id_token
                ),
                'didactic_do_export' => $this->ui_factory->table()->action()->single(
                    $this->lng->txt('didactic_do_export'),
                    $url_builder->withParameter($action_parameter_token, 'exportTemplate'),
                    $row_id_token
                ),
                'confirmDelete' => $this->ui_factory->table()->action()->multi(
                    $this->lng->txt('delete'),
                    $url_builder->withParameter($action_parameter_token, 'confirmDelete'),
                    $row_id_token
                )
                    ->withAsync(),
                'activateTemplates' => $this->ui_factory->table()->action()->multi(
                    $this->lng->txt('activate'),
                    $url_builder->withParameter($action_parameter_token, 'activateTemplates'),
                    $row_id_token
                ),
                'deactivateTemplates' => $this->ui_factory->table()->action()->multi(
                    $this->lng->txt('deactivate'),
                    $url_builder->withParameter($action_parameter_token, 'deactivateTemplates'),
                    $row_id_token
                )
            ];
        }

        return $this->ui_factory->table()->data(
            $this->lng->txt('didactic_available_templates'),
            $columns,
            $data_retrieval
        )
            ->withActions($actions)
            ->withRequest($this->http->request());
    }

    public function getHTML(
        ilDidacticTemplateSettingsTableDataRetrieval $data_retrieval
    ): string {
        return $this->renderer->render(
            $this->createTable($data_retrieval)
        );
    }
}
