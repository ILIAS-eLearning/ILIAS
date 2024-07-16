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

namespace ILIAS\AdministrativeNotification;

use ilADNAbstractGUI;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\URI;
use ILIAS\UI\URLBuilderToken;

/**
 *
 */
class Table
{
    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\UI\Renderer $ui_renderer;
    private \ilCtrlInterface $ctrl;
    private \ilLanguage $lng;
    private URLBuilder $url_builder;
    private URLBuilderToken $id_token;

    protected array $components = [];

    public function __construct(
        private \ilADNNotificationGUI $calling_gui,
    ) {
        global $DIC;
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];

        $this->url_builder = $this->initURIBuilder();
        $columns = $this->initColumns();
        $actions = $this->initActions();
        $data_retrieval = new DataRetrieval();

        $this->components[] = $this->ui_factory->table()->data(
            $this->lng->txt('notifications'),
            $columns,
            $data_retrieval
        )->withActions($actions)->withRequest(
            $DIC->http()->request()
        );
    }

    private function initURIBuilder(): URLBuilder
    {
        $url_builder = new URLBuilder(
            $this->getURI(\ilBiblFieldFilterGUI::CMD_STANDARD)
        );

        // these are the query parameters this instance is controlling
        $query_params_namespace = ['msg', 'notifications'];
        [$url_builder, $this->id_token] = $url_builder->acquireParameters(
            $query_params_namespace,
            ilADNAbstractGUI::IDENTIFIER
        );

        return $url_builder;
    }

    protected function initColumns(): array
    {
        return [
            'title' => $this->ui_factory->table()->column()->text($this->lng->txt('msg_title')),
            'type' => $this->ui_factory->table()->column()->text($this->lng->txt('msg_type')),
            'languages' => $this->ui_factory->table()->column()->text($this->lng->txt('msg_languages')),
            'type_during_event' => $this->ui_factory->table()->column()->text($this->lng->txt('msg_type_during_event')),
            'event_start' => $this->ui_factory->table()->column()->text($this->lng->txt('msg_event_date_start')),
            'event_end' => $this->ui_factory->table()->column()->text($this->lng->txt('msg_event_date_end')),
            'display_start' => $this->ui_factory->table()->column()->text($this->lng->txt('msg_display_date_start')),
            'display_end' => $this->ui_factory->table()->column()->text($this->lng->txt('msg_display_date_end'))
        ];
    }

    protected function initActions(): array
    {
        $actions = [
            'edit' => $this->ui_factory->table()->action()->single(
                $this->lng->txt("btn_edit"),
                $this->url_builder->withURI($this->getURI(\ilADNNotificationGUI::CMD_EDIT)),
                $this->id_token
            ),
            'delete' => $this->ui_factory->table()->action()->standard(
                $this->lng->txt("btn_delete"),
                $this->url_builder->withURI($this->getURI(\ilADNNotificationGUI::CMD_CONFIRM_DELETE)),
                $this->id_token
            )->withAsync(true),
            'reset' => $this->ui_factory->table()->action()->standard(
                $this->lng->txt("btn_reset"),
                $this->url_builder->withURI($this->getURI(\ilADNNotificationGUI::CMD_RESET)),
                $this->id_token
            ),
            'duplicate' => $this->ui_factory->table()->action()->single(
                $this->lng->txt("btn_duplicate"),
                $this->url_builder->withURI($this->getURI(\ilADNNotificationGUI::CMD_DUPLICATE)),
                $this->id_token
            ),
        ];

        return $actions;
    }

    /**
     * @description Unfortunately, I have not yet found an easier way to generate this URI. However, it is important
     * that it points to the calling-gui
     */
    protected function getURI(string $command): URI
    {
        return new URI(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTarget(
                $this->calling_gui,
                $command
            )
        );
    }

    public function getHTML(): string
    {
        return $this->ui_renderer->render($this->components);
    }

    public function getUrlBuilder(): URLBuilder
    {
        return $this->url_builder;
    }

    public function getIdToken(): URLBuilderToken
    {
        return $this->id_token;
    }

}
