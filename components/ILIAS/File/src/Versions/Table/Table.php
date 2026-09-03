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

namespace ILIAS\File\Versions\Table;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\URI;
use ILIAS\ResourceStorage\Revision\RevisionStatus;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class Table
{
    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;
    private \ilCtrlInterface $ctrl;
    private \ilLanguage $lng;
    private URLBuilder $url_builder;
    private URLBuilderToken $id_token;
    private \ILIAS\HTTP\Services $http;
    private DataFactory $data_factory;

    public function __construct(
        private readonly \ilFileVersionsGUI $calling_gui
    ) {
        global $DIC;
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->http = $DIC->http();
        $this->data_factory = new DataFactory();

        $this->url_builder = $this->initURIBuilder();
    }

    public function getIdToken(): URLBuilderToken
    {
        return $this->id_token;
    }

    public function getUrlBuilder(): URLBuilder
    {
        return $this->url_builder;
    }

    public function getHTML(): string
    {
        return $this->ui_renderer->render([$this->buildTable()]);
    }

    private function buildTable(): \ILIAS\UI\Component\Table\Data
    {
        $file = $this->calling_gui->getFile();
        $irss = $this->getIRSS();
        $rid = $irss->manage()->find($file->getResourceId());
        $revision = $irss->manage()->getCurrentRevisionIncludingDraft($rid);
        $amount_of_versions = count($irss->manage()->getResource($rid)->getAllRevisionsIncludingDraft());
        $current_version_is_draft = $revision->getStatus() === RevisionStatus::DRAFT;
        $current_version = $file->getVersion(true);

        $data_retrieval = new DataRetrieval(
            $file,
            $current_version,
            $current_version_is_draft,
            $amount_of_versions,
            $this->ctrl,
            $this->calling_gui,
            $this->lng,
            $this->ui_factory
        );

        return $this->ui_factory->table()
            ->data($data_retrieval, $this->lng->txt('versions'), $this->initColumns())
            ->withActions($this->initActions())
            ->withOrder(new Order('version', Order::DESC))
            ->withRequest($this->http->request());
    }

    private function initURIBuilder(): URLBuilder
    {
        $url_builder = new URLBuilder($this->getURI(\ilFileVersionsGUI::CMD_DEFAULT));
        [$url_builder, $this->id_token] = $url_builder->acquireParameters(
            ['file', 'versions'],
            \ilFileVersionsGUI::HIST_ID
        );
        return $url_builder;
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Column\Column>
     */
    private function initColumns(): array
    {
        $df = $this->data_factory;
        $col = $this->ui_factory->table()->column();

        return [
            'version' => $col->number($this->lng->txt('version')),
            'filename' => $col->link($this->lng->txt('filename')),
            'date' => $col->date($this->lng->txt('date'), $df->dateFormat()->withTime24($df->dateFormat()->germanShort())),
            'uploaded_by' => $col->text($this->lng->txt('file_uploaded_by')),
            'versionname' => $col->text($this->lng->txt('versionname')),
            'filesize' => $col->text($this->lng->txt('filesize'))->withIsSortable(false),
            'status' => $col->text($this->lng->txt('status'))->withIsSortable(false),
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    private function initActions(): array
    {
        $a = $this->ui_factory->table()->action();

        return [
            DataRetrieval::ACTION_DELETE => $a->standard(
                $this->lng->txt('delete'),
                $this->url_builder->withURI($this->getURI(\ilFileVersionsGUI::CMD_RENDER_DELETE_SELECTED_VERSIONS_MODAL)),
                $this->id_token
            )->withAsync(true),
            DataRetrieval::ACTION_ROLLBACK => $a->single(
                $this->lng->txt('file_rollback'),
                $this->url_builder->withURI($this->getURI(\ilFileVersionsGUI::CMD_ROLLBACK_VERSION)),
                $this->id_token
            ),
            DataRetrieval::ACTION_PUBLISH => $a->single(
                $this->lng->txt('file_publish'),
                $this->url_builder->withURI($this->getURI(\ilFileVersionsGUI::CMD_PUBLISH)),
                $this->id_token
            ),
            DataRetrieval::ACTION_UNPUBLISH => $a->single(
                $this->lng->txt('file_unpublish'),
                $this->url_builder->withURI($this->getURI(\ilFileVersionsGUI::CMD_UNPUBLISH)),
                $this->id_token
            ),
        ];
    }

    private function getURI(string $command): URI
    {
        return new URI(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget($this->calling_gui, $command)
        );
    }

    private function getIRSS(): IRSS
    {
        global $DIC;
        return $DIC->resourceStorage();
    }
}
