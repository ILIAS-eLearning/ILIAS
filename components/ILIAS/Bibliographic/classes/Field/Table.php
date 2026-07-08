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

namespace ILIAS\Bibliographic\Field;

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Table\Ordering;
use ilBiblAdminFieldGUI;
use ilBiblTranslationGUI;
use ILIAS\Bibliographic\Field\DataRetrieval;
use ILIAS\Data\URI;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

/**
 * Class ilTable
 *
 */
class Table
{
    private Factory $ui_factory;
    private Renderer $ui_renderer;
    private \ilCtrlInterface $ctrl;
    private \ilLanguage $lng;
    private URLBuilder $url_builder;
    private URLBuilderToken $id_token;
    private Ordering $table;

    protected array $components = [];

    /**
     * ilTable constructor.
     */
    public function __construct(
        private \ilBiblAdminFieldGUI $calling_gui,
        \ilBiblAdminFactoryFacadeInterface $facade
    ) {
        global $DIC;
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];

        $this->url_builder = $this->initURIBuilder();
        $columns = $this->initColumns();
        $actions = $this->initActions();
        $data_retrieval = new DataRetrieval(
            $facade,
            $calling_gui->checkPermissionBoolAndReturn('write')
        );

        $this->components[] = $this->table = $this->ui_factory->table()->ordering(
            $data_retrieval,
            new URI(
                ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTarget($this->calling_gui, \ilBiblAdminFieldGUI::CMD_SAVE_ORDERING)
            ),
            $this->lng->txt('filter'),
            $columns,
        )->withActions($actions)->withRequest(
            $DIC->http()->request()
        );
    }

    private function initURIBuilder(): URLBuilder
    {
        $url_builder = new URLBuilder(
            $this->getURI(\ilBiblAdminFieldGUI::CMD_STANDARD)
        );

        // these are the query parameters this instance is controlling
        $query_params_namespace = ['bibl'];
        [$url_builder, $this->id_token] = $url_builder->acquireParameters(
            $query_params_namespace,
            ilBiblAdminFieldGUI::FIELD_IDENTIFIER
        );
        return $url_builder;
    }

    protected function initColumns(): array
    {
        return [
            'identifier' => $this->ui_factory->table()->column()->text($this->lng->txt('identifier')),
            'data_type' => $this->ui_factory->table()->column()->text($this->lng->txt('translation')),
            'is_standard_field' => $this->ui_factory->table()->column()->text($this->lng->txt('standard'))
        ];
    }

    protected function initActions(): array
    {
        return [
            'translate' => $this->ui_factory->table()->action()->single(
                $this->lng->txt("translate"),
                $this->url_builder->withURI($this->getURIWithTargetClass(\ilBiblTranslationGUI::class, \ilBiblTranslationGUI::CMD_DEFAULT)),
                $this->id_token
            )
        ];
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

    protected function getURIWithTargetClass(string $target_gui, string $command): URI
    {
        return new URI(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass(
                $target_gui,
                $command
            )
        );
    }

    public function getHTML(): string
    {
        return $this->ui_renderer->render($this->components);
    }

    public function getOrdering(): array
    {
        return $this->table->getData();
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
