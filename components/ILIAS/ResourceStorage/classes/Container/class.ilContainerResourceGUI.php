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
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizer;
use ILIAS\Filesystem\Stream\Streams;
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

    /**
     * Chunks of an unfinished upload are buffered below this directory in the
     * temp filesystem, one sub-directory per upload.
     */
    private const CHUNK_DIRECTORY = 'container_chunked_uploads';

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
    private Filesystem $temp_filesystem;
    private ilLogger $logger;
    private int $user_id;

    private bool $is_chunked = false;
    private string $uuid = '';
    private int $chunk_index = 0;
    private int $amount_of_chunks = 0;
    private int $chunk_byte_offset = 0;
    private int $chunk_total_size = 0;

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
        $this->temp_filesystem = $DIC->filesystem()->temp();
        $this->logger = ilLoggerFactory::getLogger('irss');
        $this->user_id = $DIC->user()->getId();

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

    private function abortWithPermissionDenied(): void
    {
        $this->main_tpl->setOnScreenMessage('failure', $this->language->txt('msg_no_perm_read'), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    public function executeCommand(): void
    {
        if ($this->view_request->handleViewTitle()) {
            $title = $this->view_request->getTitle();
            $this->main_tpl->setTitle($title);
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
        $this->readChunkInformation();
        $this->upload->process();
        if (!$this->upload->hasUploads()) {
            return;
        }

        $this->sendHandlerResult(
            $this->is_chunked ? $this->storeChunk() : $this->storeUploads()
        );
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

    private function getPathsFromRequest(): array
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
        $paths = $this->getPathsFromRequest();

        // the message has to reflect what unzip() actually did, a hardcoded one
        // reports a success even when nothing was added to the container
        $success = $paths !== [] && $this->view_request->getWrapper()->unzip($paths[0]);

        $this->main_tpl->setOnScreenMessage(
            $success ? 'success' : 'failure',
            $this->language->txt($success ? 'rids_appended' : 'rids_appended_failed'),
            true
        );

        $this->ctrl->redirect($this, self::CMD_INDEX);
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


    // UPLOAD HELPERS

    /**
     * Adds every file of the request to the container. A request can carry more
     * than one file, therefore every result has to be taken into account:
     * reporting the outcome of the last one would hide files rejected by a
     * pre-processor from the user.
     */
    private function storeUploads(): BasicHandlerResult
    {
        $container = $this->view_configuration->getContainer();
        $results = $this->upload->getResults();
        $stored_all = $results !== [];
        $message = '';

        foreach ($results as $result) {
            if (!$result->isOK()) {
                $stored_all = false;
                $message = $result->getStatus()->getMessage();
                continue;
            }
            // store to zip
            if (!$this->irss->manageContainer()->addUploadToContainer(
                $container->getIdentification(),
                $result,
                $this->view_request->getPath()
            )) {
                $stored_all = false;
            }
        }

        return $stored_all ? $this->ok() : $this->failed($message);
    }

    /**
     * Chunks arrive one request at a time and cannot be added to the container
     * on their own: they all carry the same file name and each one would replace
     * the entry written by the chunk before it. They are therefore appended to a
     * single file in the temp filesystem, and only once the last chunk has
     * arrived that file is added to the container.
     */
    private function storeChunk(): BasicHandlerResult
    {
        $results = $this->upload->getResults();
        $result = end($results);
        if (!$result instanceof UploadResult) {
            return $this->chunkFailed('the request carried no upload result');
        }
        if (!$result->isOK()) {
            $this->logChunkFailure('rejected while processing: ' . $result->getStatus()->getMessage());
            return $this->discardChunks($this->failed($result->getStatus()->getMessage()));
        }

        // A fixed name keeps the uploaded file name out of the path the chunks are
        // written to. It has to end in the clean suffix: the temp filesystem is
        // wrapped in a FilesystemWhitelistDecorator, which rewrites the suffix of
        // anything written to it but leaves the path of a read untouched - a
        // buffer under any other suffix could be written but never read back.
        $part_file = $this->getChunkDirectory() . '/upload.' . FilenameSanitizer::CLEAN_FILE_SUFFIX;

        try {
            if (!$this->temp_filesystem->has($part_file)) {
                $this->temp_filesystem->write($part_file, '');
            }
            $part_path = $this->getLocalPath($part_file);

            // the dropzone sends the chunks of a file strictly in order, an
            // offset that does not match what has been written so far means the
            // assembled file would be garbage
            clearstatcache(true, $part_path);
            $buffered = filesize($part_path);
            if ($buffered !== $this->chunk_byte_offset) {
                return $this->chunkFailed(
                    "chunk starts at byte $this->chunk_byte_offset but $buffered bytes are buffered"
                );
            }

            $this->appendToFile($result->getPath(), $part_path);

            if (($this->chunk_index + 1) < $this->amount_of_chunks) {
                return new BasicHandlerResult(
                    self::P_PATH,
                    BasicHandlerResult::STATUS_PARTIAL,
                    '-',
                    'chunk upload OK'
                );
            }

            clearstatcache(true, $part_path);
            $assembled_size = filesize($part_path);
            if ($assembled_size !== $this->chunk_total_size) {
                return $this->chunkFailed(
                    "assembled $assembled_size bytes, the upload announced $this->chunk_total_size"
                );
            }

            // The container derives the name of the entry from the name of the
            // file, so the assembled file has to carry the uploaded name and be
            // alone in its directory. Moving it is done on the local path: going
            // through the temp filesystem would hand the name to the whitelist
            // decorator, which would rewrite the suffix the user uploaded.
            $assembly_directory = dirname($part_path) . '/file';
            $assembled_path = $assembly_directory . '/' . basename($result->getName());

            if (!is_dir($assembly_directory) && !mkdir($assembly_directory, 0700, true) && !is_dir($assembly_directory)) {
                return $this->chunkFailed("could not create the directory '$assembly_directory'");
            }
            if (!rename($part_path, $assembled_path)) {
                return $this->chunkFailed("could not move the assembled file to '$assembled_path'");
            }

            // adding the directory hands the assembled file to ZipArchive::addFile(),
            // which reads it lazily - unlike addStreamToContainer(), which would
            // pull the whole file into memory
            $added = $this->irss->manageContainer()->addDirectoryToContainer(
                $this->view_configuration->getContainer()->getIdentification(),
                $assembly_directory,
                $this->view_request->getPath()
            );

            if (!$added) {
                return $this->chunkFailed('the container refused the assembled file');
            }

            return $this->discardChunks($this->ok());
        } catch (\Throwable $exception) {
            return $this->chunkFailed($exception::class . ': ' . $exception->getMessage());
        }
    }

    /**
     * Every way a chunked upload can fail ends up here. Without it the user is
     * left with nothing but a generic message and no trace of what went wrong.
     */
    private function chunkFailed(string $reason): BasicHandlerResult
    {
        $this->logChunkFailure($reason);

        return $this->discardChunks($this->failed(''));
    }

    private function logChunkFailure(string $reason): void
    {
        $this->logger->warning(sprintf(
            'chunked upload %s of user %d failed on chunk %d of %d: %s',
            $this->uuid,
            $this->user_id,
            $this->chunk_index + 1,
            $this->amount_of_chunks,
            $reason
        ));
    }

    /**
     * Chunks are kept apart per user: the uuid identifying an upload is chosen by
     * the client and must not allow one user to write into the upload of another.
     */
    private function getChunkDirectory(): string
    {
        return self::CHUNK_DIRECTORY . '/' . $this->user_id . '/' . $this->uuid;
    }

    /**
     * The dropzone announces a chunked upload with these fields in the body of
     * every single chunk request.
     */
    private function readChunkInformation(): void
    {
        $body = $this->http->request()->getParsedBody();

        $uuid = (string) ($body['dzuuid'] ?? '');
        $amount_of_chunks = (int) ($body['dztotalchunkcount'] ?? 0);

        // the uuid is used as a directory name, therefore nothing but the format
        // the dropzone generates is accepted
        if ($amount_of_chunks < 1 || preg_match('/^[0-9a-f-]{36}$/i', $uuid) !== 1) {
            return;
        }

        $this->is_chunked = true;
        $this->uuid = $uuid;
        $this->amount_of_chunks = $amount_of_chunks;
        $this->chunk_index = (int) ($body['dzchunkindex'] ?? 0);
        $this->chunk_byte_offset = (int) ($body['dzchunkbyteoffset'] ?? 0);
        $this->chunk_total_size = (int) ($body['dztotalfilesize'] ?? 0);
    }

    private function getLocalPath(string $path_in_temp_filesystem): string
    {
        $stream = $this->temp_filesystem->readStream($path_in_temp_filesystem);
        $uri = $stream->getMetadata()['uri'];
        $stream->close();

        return (string) $uri;
    }

    private function appendToFile(string $source_path, string $target_path): void
    {
        $source = fopen($source_path, 'rb');
        $target = fopen($target_path, 'ab');

        try {
            if ($source === false || $target === false) {
                throw new \RuntimeException("Could not append '$source_path' to '$target_path'.");
            }
            stream_copy_to_stream($source, $target);
        } finally {
            if ($source !== false) {
                fclose($source);
            }
            if ($target !== false) {
                fclose($target);
            }
        }
    }

    /**
     * Removes everything buffered for the current upload, whether it completed
     * or failed. Chunks of an upload the user abandoned are never reported here
     * and stay behind until the temp directory is cleaned up.
     */
    private function discardChunks(BasicHandlerResult $result): BasicHandlerResult
    {
        try {
            if ($this->temp_filesystem->hasDir($this->getChunkDirectory())) {
                $this->temp_filesystem->deleteDir($this->getChunkDirectory());
            }
        } catch (\Throwable) {
            // a leftover directory must not turn a successful upload into a failure
        }

        return $result;
    }

    private function ok(): BasicHandlerResult
    {
        return new BasicHandlerResult(self::P_PATH, BasicHandlerResult::STATUS_OK, '-', 'file upload OK');
    }

    private function failed(string $message): BasicHandlerResult
    {
        return new BasicHandlerResult(
            self::P_PATH,
            BasicHandlerResult::STATUS_FAILED,
            '-',
            $message !== '' ? $message : $this->language->txt('rids_appended_failed')
        );
    }

    private function sendHandlerResult(BasicHandlerResult $result): never
    {
        $response = $this->http->response()->withBody(Streams::ofString(json_encode($result)));
        $this->http->saveResponse($response);
        $this->http->sendResponse();
        $this->http->close();
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
        return true;
    }
}
