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

use ILIAS\UI\Renderer;
use ILIAS\Refinery\Factory;
use ILIAS\HTTP\Services;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Filesystem\Util\Archive\Archives;
use ILIAS\components\ResourceStorage\Container\View\ActionBuilder\ActionProvider;
use ILIAS\UI\Component\Modal\InterruptiveItem\Standard;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\components\ResourceStorage\Container\View\Configuration;
use ILIAS\components\ResourceStorage\Container\View\Request;
use ILIAS\components\ResourceStorage\Container\View\ViewFactory;
use ILIAS\components\ResourceStorage\Container\DataProvider\TableDataProvider;
use ILIAS\components\ResourceStorage\URLSerializer;
use ILIAS\components\ResourceStorage\Container\View\ActionBuilder;
use ILIAS\components\ResourceStorage\Container\View\ViewControlBuilder;
use ILIAS\components\ResourceStorage\Container\View\UploadBuilder;
use ILIAS\components\ResourceStorage\Container\View\PreviewDefinition;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\components\ResourceStorage\Container\View\StandardActionProvider;
use ILIAS\components\ResourceStorage\Container\View\CombinedActionProvider;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ilContainerResourceGUI implements UploadHandler
{
    use URLSerializer;

    /**
     * @var string
     */
    public const P_PATH = 'path';
    /**
     * @var string
     */
    public const P_PATHS = 'paths';

    /**
     * @var string
     */
    public const CMD_INDEX = 'index';
    /**
     * @var string
     */
    public const CMD_INFO = 'info';
    /**
     * @var string
     */
    public const CMD_UPLOAD = 'upload';
    /**
     * @var string
     */
    public const CMD_POST_UPLOAD = 'postUpload';
    /**
     * @var string
     */
    public const CMD_REMOVE = 'remove';
    /**
     * @var string
     */
    public const CMD_DOWNLOAD = 'download';
    /**
     * @var string
     */
    public const CMD_DOWNLOAD_ZIP = 'downloadZIP';

    /**
     * @var string
     */
    public const CMD_UNZIP = 'unzip';
    /**
     * @var string
     */
    public const CMD_RENDER_CONFIRM_REMOVE = 'renderConfirmRemove';
    /**
     * @var string
     */
    public const ADD_DIRECTORY = 'addDirectory';

    private ilCtrlInterface $ctrl;
    private ilGlobalTemplateInterface $main_tpl;
    private Request $view_request;
    private ViewFactory $view_factory;
    private Renderer $ui_renderer;
    private Factory $refinery;
    private Services $http;
    private ilLanguage $language;
    private \ILIAS\ResourceStorage\Services $irss;
    private ActionBuilder $action_builder;
    private ViewControlBuilder $view_control_builder;
    private \ILIAS\UI\Factory $ui_factory;
    private FileUpload $upload;
    private Archives $archive;
    private PreviewDefinition $preview_definition;
    private ActionProvider $action_provider;
    private StandardActionProvider $standard_action_provider;

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

        $this->view_request = new Request(
            $DIC->ctrl(),
            $DIC->http()->wrapper()->query(),
            $this->view_configuration
        );

        // to store paramaters needed in GUI
        $this->view_request->init($this);

        $this->action_provider = new CombinedActionProvider(
            $this->standard_action_provider = new StandardActionProvider($this->view_request),
            $this->view_configuration->getActionProvider()
        );

        $data_provider = new TableDataProvider($this->view_request);

        $this->action_builder = new ActionBuilder(
            $this->view_request,
            $this->ctrl,
            $DIC->ui()->factory(),
            $DIC->language(),
            $this->irss,
            $this->action_provider
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

    protected function abortWithPermissionDenied(): void
    {
        $this->main_tpl->setOnScreenMessage('failure', $this->language->txt('msg_no_perm_read'), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    public function executeCommand(): void
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
            case self::ADD_DIRECTORY:
                $this->addDirectory();
                break;
            case self::CMD_DOWNLOAD_ZIP:
                $this->downloadZIP();
                break;
        }
    }

    // RESOURCE COLLECTION GUI

    private function index(): void
    {
        global $DIC;
        $components = [];

        // Add components from Actions
        $components = array_merge(
            $components,
            $this->action_provider->getComponents()
        );

        // Add components from the selected view (currently data-table)
        foreach ($this->view_factory->getComponentProvider($this->view_request)->getComponents() as $component) {
            $components[] = $component;
        }

        $this->main_tpl->setContent(
            $this->ui_renderer->render(
                $components
            )
        );
    }

    private function downloadZIP(): never
    {
        $this->irss->consume()->download(
            $this->view_configuration->getContainer()->getIdentification()
        )->overrideFileName($this->view_request->getTitle())->run();
    }

    private function addDirectory(): void
    {
        if (!$this->view_request->canUserAdministrate()) {
            $this->abortWithPermissionDenied();
            return;
        }
        $modal = $this->standard_action_provider->getAddDirectoryModal()->withRequest($this->http->request());

        $directory_name = $modal->getData()[0] ?? '';
        if (empty($directory_name)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->language->txt('msg_error_adding_directory'), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
            return;
        }
        $directory_name = $this->view_request->getPath() . $directory_name;

        $success = $this->irss->manageContainer()->createDirectoryInsideContainer(
            $this->view_configuration->getContainer()->getIdentification(),
            $directory_name
        );

        if (!$success) {
            $this->main_tpl->setOnScreenMessage('failure', $this->language->txt('msg_error_adding_directory'), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
            return;
        }

        $this->main_tpl->setOnScreenMessage('success', $this->language->txt('msg_success_adding_directory'), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
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
        $container = $this->view_configuration->getContainer();

        foreach ($this->upload->getResults() as $result) {
            if (!$result->isOK()) {
                continue;
            }
            // store to zip
            $return = $this->irss->manageContainer()->addUploadToContainer(
                $container->getIdentification(),
                $result,
                $this->view_request->getPath()
            );
        }

        // OK
        $upload_result = new BasicHandlerResult(
            self::P_PATH,
            $return ? BasicHandlerResult::STATUS_OK : BasicHandlerResult::STATUS_FAILED,
            '-',
            'undefined error'
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
        if ($this->http->request()->getParsedBody() === []) { // nothing uploaded
            $this->main_tpl->setOnScreenMessage('failure', $this->language->txt('rids_appended_failed'), true);
        } else {
            $this->main_tpl->setOnScreenMessage('success', $this->language->txt('rids_appended'), true);
        }

        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    private function download(): never
    {
        $paths = $this->getPathsFromRequest();

        $this->view_request->getWrapper()->download(
            $paths[0]
        );
    }

    protected function getPathsFromRequest(): array
    {
        $unhash = fn(string $path): string => $this->unhash($path);
        $unhash_array = static fn(array $paths): array => array_map(
            $unhash,
            $paths
        );
        $to_string = $this->refinery->kindlyTo()->string();
        $to_array_of_strings = $this->refinery->kindlyTo()->listOf(
            $to_string
        );

        // Get item from table
        $token_name = $this->action_builder->getUrlToken()->getName();
        if ($this->http->wrapper()->query()->has($token_name)) {
            return $unhash_array(
                $this->http->wrapper()->query()->retrieve(
                    $token_name,
                    $to_array_of_strings
                ) ?? []
            );
        }

        if ($this->http->wrapper()->post()->has('interruptive_items')) {
            return $unhash_array(
                $this->http->wrapper()->post()->retrieve(
                    'interruptive_items',
                    $to_array_of_strings
                )
            );
        }

        return [];
    }

    private function unzip(): void
    {
        if (!$this->view_request->canUserAdministrate()) {
            $this->abortWithPermissionDenied();
            return;
        }
        $paths = $this->getPathsFromRequest()[0];
        $this->view_request->getWrapper()->unzip(
            $paths
        );

        $this->postUpload();
    }

    private function renderConfirmRemove(): void
    {
        if (!$this->view_request->canUserAdministrate()) {
            $this->abortWithPermissionDenied();
            return;
        }
        $paths = $this->getPathsFromRequest();

        $stream = Streams::ofString(
            $this->ui_renderer->render(
                $this->ui_factory->modal()->interruptive(
                    $this->language->txt('action_remove_zip_path'),
                    $this->language->txt('action_remove_zip_path_msg'),
                    $this->ctrl->getLinkTarget($this, self::CMD_REMOVE)
                )->withAffectedItems(
                    array_map(fn(string $path_inside_zip): Standard => $this->ui_factory->modal()->interruptiveItem()->standard(
                        $this->hash($path_inside_zip),
                        $path_inside_zip
                    ), $paths)
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
        $paths = $this->getPathsFromRequest();

        if (empty($paths)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->language->txt('msg_no_perm_read'), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
            return;
        }

        foreach ($paths as $path_inside_zip) {
            $this->irss->manageContainer()->removePathInsideContainer(
                $this->view_configuration->getContainer()->getIdentification(),
                $path_inside_zip
            );
        }

        $this->main_tpl->setOnScreenMessage('success', $this->language->txt('msg_paths_deleted'), true);
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
                $rid_strings = explode(',', (string) $rid_string);
            } catch (ConstraintViolationException) {
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

        if ($rid_strings[0] === 'ALL_OBJECTS') {
            return $this->view_request->getWrapper()->getResourceIdentifications();
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
        return self::P_PATH;
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
