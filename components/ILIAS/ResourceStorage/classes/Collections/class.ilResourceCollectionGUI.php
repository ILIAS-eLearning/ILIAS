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

use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\components\ResourceStorage\Collections\View\Configuration;
use ILIAS\components\ResourceStorage\Collections\View\Request;
use ILIAS\components\ResourceStorage\Collections\View\ViewFactory;
use ILIAS\components\ResourceStorage\Collections\DataProvider\TableDataProvider;
use ILIAS\components\ResourceStorage\BinToHexSerializer;
use ILIAS\components\ResourceStorage\Collections\View\ActionBuilder;
use ILIAS\components\ResourceStorage\Collections\View\ViewControlBuilder;
use ILIAS\components\ResourceStorage\Collections\View\UploadBuilder;
use ILIAS\components\ResourceStorage\Collections\View\PreviewDefinition;
use ILIAS\Filesystem\Util\Archive\UnzipOptions;
use ILIAS\Refinery\ConstraintViolationException;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilResourceCollectionGUI implements UploadHandler
{
    use BinToHexSerializer;

    public const P_RESOURCE_ID = 'resource_id';
    public const P_RESOURCE_IDS = 'resource_ids';

    public const CMD_INDEX = 'index';
    public const CMD_INFO = 'info';
    public const CMD_UPLOAD = 'upload';
    public const CMD_POST_UPLOAD = 'postUpload';
    public const CMD_REMOVE = 'remove';
    public const CMD_DOWNLOAD = 'download';

    public const CMD_UNZIP = 'unzip';
    public const CMD_RENDER_CONFIRM_REMOVE = 'renderConfirmRemove';
    private ilCtrlInterface $ctrl;
    private ilGlobalTemplateInterface $main_tpl;
    private Request $view_request;
    private ViewFactory $view_factory;
    private \ILIAS\UI\Renderer $ui_renderer;
    private \ILIAS\Refinery\Factory $refinery;
    private \ILIAS\HTTP\Services $http;
    private ilLanguage $language;
    private \ILIAS\ResourceStorage\Services $irss;
    private ActionBuilder $action_builder;
    private ViewControlBuilder $view_control_builder;
    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\FileUpload\FileUpload $upload;
    private \ILIAS\Filesystem\Util\Archive\Archives $archive;
    private PreviewDefinition $preview_definition;

    final public function __construct(
        private Configuration $view_configuration
    ) {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ui_factory = $DIC->ui()->factory();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->language = $DIC->language();
        $this->language->loadLanguageModule('irss');
        $this->irss = $DIC->resourceStorage();
        $this->upload = $DIC->upload();
        $this->archive = $DIC->archives();
        $this->preview_definition = new PreviewDefinition();

        $this->view_request = new Request(
            $DIC->ctrl(),
            $DIC->http()->wrapper()->query(),
            $this->view_configuration
        );

        $data_provider = new TableDataProvider($this->view_request);

        $this->action_builder = new ActionBuilder(
            $this->view_request,
            $this->ctrl,
            $DIC->ui()->factory(),
            $DIC->language(),
            $this->irss
        );

        $view_control_builder = new ViewControlBuilder(
            $this->view_request,
            $data_provider,
            $this->ctrl,
            $DIC->ui()->factory(),
            $DIC->language()
        );

        $upload_builder = new UploadBuilder(
            $this->view_request,
            $this->ctrl,
            $DIC->ui()->factory(),
            $DIC->language(),
            $this
        );

        $this->view_factory = new ViewFactory(
            $data_provider,
            $this->action_builder,
            $view_control_builder,
            $upload_builder
        );
    }

    // CMD CLASS

    /**
     * @return ResourceIdentification
     * @throws ilCtrlException
     */
    protected function checkResourceOrRedirect(): ResourceIdentification
    {
        $rid = $this->getResourceIdsFromRequest()[0];
        if ($rid === null || !$this->view_request->getCollection()->isIn($rid)) {
            $this->abortWithPermissionDenied();
        }
        return $rid;
    }

    protected function abortWithPermissionDenied(): void
    {
        $this->main_tpl->setOnScreenMessage('failure', $this->language->txt('msg_no_perm_read'), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    final public function executeCommand(): void
    {
        if ($this->view_request->handleViewTitle()) {
            $title = $this->view_request->getTitle();
            if ($title !== null) {
                $this->main_tpl->setTitle($title);
            }
            $description = $this->view_request->getDescription();
            if ($description !== null) {
                $this->main_tpl->setDescription($description);
            }
        }

        $this->view_request->init($this);

        switch ($this->ctrl->getCmd(self::CMD_INDEX)) {
            case self::CMD_INDEX:
                $this->index();
                break;
            case self::CMD_UPLOAD:
                $this->upload();
                break;
            case self::CMD_POST_UPLOAD:
                $this->postUpload();
                break;
            case self::CMD_REMOVE:
                $this->remove();
                break;
            case self::CMD_DOWNLOAD:
                $this->download();
                break;
            case self::CMD_UNZIP:
                $this->unzip();
                break;
            case self::CMD_RENDER_CONFIRM_REMOVE:
                $this->renderConfirmRemove();
                break;
        }
    }

    // RESOURCE COLLECTION GUI

    private function index(): void
    {
        $provider = $this->view_factory->getComponentProvider($this->view_request);
        $components = [];
        foreach ($provider->getComponents() as $component) {
            $components[] = $component;
        }

        $this->main_tpl->setContent($this->ui_renderer->render($components));
    }

    public function upload(): void
    {
        if (!$this->view_request->canUserUplaod()) {
            $this->abortWithPermissionDenied();
            return;
        }
        $this->upload->process();
        if (!$this->upload->hasUploads()) {
            return;
        }
        $collection = $this->view_request->getCollection();
        foreach ($this->upload->getResults() as $result) {
            if (!$result->isOK()) {
                continue;
            }
            $rid = $this->irss->manage()->upload(
                $result,
                $this->view_configuration->getStakeholder()
            );
            $collection->add($rid);

            // ensure flavour
            $this->irss->flavours()->ensure(
                $rid,
                $this->preview_definition
            );
        }
        $this->irss->collection()->store($collection);
        $upload_result = new BasicHandlerResult(
            self::P_RESOURCE_ID,
            BasicHandlerResult::STATUS_OK,
            $rid->serialize(),
            ''
        );
        $response = $this->http->response()->withBody(Streams::ofString(json_encode($upload_result)));
        $this->http->saveResponse($response);
        $this->http->sendResponse();
        $this->http->close();
    }

    private function postUpload(): void
    {
        if (!$this->view_request->canUserUplaod()) {
            $this->abortWithPermissionDenied();
            return;
        }
        $this->main_tpl->setOnScreenMessage('success', $this->language->txt('rids_appended'), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    private function download(): void
    {
        $rid = $this->checkResourceOrRedirect();
        $this->irss->consume()->download($rid)->run();
    }

    private function unzip(): void
    {
        if (!$this->view_request->canUserAdministrate()) {
            $this->abortWithPermissionDenied();
            return;
        }
        $rid = $this->checkResourceOrRedirect();
        $zip_stream = $this->irss->consume()->stream($rid)->getStream();

        $collection = $this->view_request->getCollection();

        $unzip_options = (new UnzipOptions())->withFlat(true);

        foreach ($this->archive->unzip($zip_stream, $unzip_options)->getFileStreams() as $stream) {
            $rid = $this->irss->manage()->stream(
                Streams::ofString($stream->getContents()),
                $this->view_configuration->getStakeholder(),
                basename($stream->getMetadata()['uri'])
            );
            $collection->add($rid);

            // ensure flavour
            try {
                $this->irss->flavours()->ensure(
                    $rid,
                    $this->preview_definition
                );
            } catch (\Throwable $e) {
            }
        }
        $this->irss->collection()->store($collection);
        $this->postUpload();
    }

    private function renderConfirmRemove(): void
    {
        if (!$this->view_request->canUserAdministrate()) {
            $this->abortWithPermissionDenied();
            return;
        }
        $rids = $this->getResourceIdsFromRequest();
        $stream = Streams::ofString(
            $this->ui_renderer->render(
                $this->ui_factory->modal()->interruptive(
                    $this->language->txt('action_remove_resource'),
                    $this->language->txt('action_remove_resource_msg'),
                    $this->ctrl->getLinkTarget($this, self::CMD_REMOVE)
                )->withAffectedItems(
                    array_map(function (ResourceIdentification $rid) {
                        $revision = $this->irss->manage()->getCurrentRevision($rid);

                        return $this->ui_factory->modal()->interruptiveItem()->standard(
                            $this->hash($rid->serialize()),
                            $revision->getTitle()
                        );
                    }, $rids)
                )
            )
        );
        $this->http->saveResponse($this->http->response()->withBody($stream));
        $this->http->sendResponse();
        $this->http->close();
    }

    private function remove(): void
    {
        if (!$this->view_request->canUserAdministrate()) {
            $this->abortWithPermissionDenied();
            return;
        }
        $rids = $this->getResourceIdsFromRequest();
        if (empty($rids)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->language->txt('msg_no_perm_read'), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
            return;
        }
        foreach ($rids as $rid) {
            if (!$this->view_request->getCollection()->isIn($rid)) {
                $this->main_tpl->setOnScreenMessage('failure', $this->language->txt('msg_no_perm_read'), true);
                $this->ctrl->redirect($this, self::CMD_INDEX);
                return;
            }
        }

        foreach ($rids as $rid) {
            $this->irss->manage()->remove($rid, $this->view_configuration->getStakeholder());
        }

        $this->main_tpl->setOnScreenMessage('success', $this->language->txt('rids_deleted'), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    // REQUEST HELPERS

    /**
     * @return ResourceIdentification[]
     */
    private function getResourceIdsFromRequest(): array
    {
        $token = $this->action_builder->getUrlToken();
        $wrapper = $this->http->wrapper();
        $to_string = $this->refinery->kindlyTo()->string();
        $to_array_of_string = $this->refinery->to()->listOf($to_string);
        $rid_string = null;

        if ($wrapper->query()->has($token->getName())) {
            try {
                $rid_string = $wrapper->query()->retrieve(
                    $token->getName(),
                    $to_string
                );
                $rid_strings = explode(',', $rid_string);
            } catch (ConstraintViolationException $e) {
                $rid_strings = $wrapper->query()->retrieve(
                    $token->getName(),
                    $to_array_of_string
                );
            }
        }

        if ($wrapper->post()->has('interruptive_items')) {
            $rid_strings = $wrapper->post()->retrieve(
                'interruptive_items',
                $to_array_of_string
            );
        }

        if($rid_strings[0] === 'ALL_OBJECTS') {
            return $this->view_request->getCollection()->getResourceIdentifications();
        }

        if ($rid_strings === []) {
            return [];
        }
        $resource_identifications = [];
        foreach ($rid_strings as $rid_string) {
            $resource_identification = $this->irss->manage()->find($this->unhash($rid_string));
            if ($resource_identification === null) {
                continue;
            }
            $resource_identifications[] = $resource_identification;
        }
        return $resource_identifications;
    }

    // UPLOAD HANDLER
    public function getFileIdentifierParameterName(): string
    {
        return self::P_RESOURCE_ID;
    }

    public function getUploadURL(): string
    {
        return $this->ctrl->getLinkTarget($this, self::CMD_UPLOAD);
    }

    public function getFileRemovalURL(): string
    {
        return '';
    }

    public function getExistingFileInfoURL(): string
    {
        return $this->ctrl->getLinkTarget($this, self::CMD_INFO);
    }

    public function getInfoForExistingFiles(array $file_ids): array
    {
        return [];
    }

    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        return null;
    }

    public function supportsChunkedUploads(): bool
    {
        return false;
    }
}
