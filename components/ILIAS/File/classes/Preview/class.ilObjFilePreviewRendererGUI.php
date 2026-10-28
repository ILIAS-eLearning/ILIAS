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
use ILIAS\UI\Renderer;
use ILIAS\ResourceStorage\Services;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\UI\Component\Modal\LightboxImagePage;
use ILIAS\ResourceStorage\Flavour\Definition\FitToSquare;
use ILIAS\Modules\File\Preview\SettingsFactory;
use ILIAS\WOPI\Discovery\ActionDBRepository;
use ILIAS\WOPI\Discovery\Action;
use ILIAS\WOPI\Embed\EmbeddedApplication;
use ILIAS\WOPI\Embed\Renderer as WOPIRenderer;
use ILIAS\File\Capabilities\Permissions;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilObjFilePreviewRendererGUI implements ilCtrlBaseClassInterface
{
    public const P_RID = "rid";
    public const CMD_GET_ASYNC_MODAL = 'getAsyncModal';
    public const CMD_GET_ASYNC_WOPI_MODAL = 'getAsyncWopiModal';

    /**
     * @readonly
     */
    private ilDBInterface $db;
    /**
     * @readonly
     */
    private Factory $ui_factory;
    /**
     * @readonly
     */
    private Renderer $ui_renderer;
    /**
     * @readonly
     */
    private ilCtrlInterface $ctrl;
    private ?ResourceIdentification $rid = null;
    /**
     * @readonly
     */
    private Services $irss;
    /**
     * @readonly
     */
    private \ILIAS\HTTP\Services $http;
    /**
     * @readonly
     */
    private WrapperFactory $http_wrapper;
    /**
     * @readonly
     */
    private \ILIAS\Refinery\Factory $refinery;
    /**
     * @readonly
     */
    private FlavourDefinition $flavour_definition;
    /**
     * @readonly
     */
    private ilAccessHandler $access;
    /**
     * @readonly
     */
    private ilLanguage $language;
    /**
     * @readonly
     */
    private int $preview_size;
    /**
     * @readonly
     */
    private int $pages_to_extract;
    private bool $activated = false;
    private string $file_name = '';
    /**
     * @readonly
     */
    private FlavourDefinition $fallback_flavour_definition;
    private ActionDBRepository $action_repository;
    private string $suffix = '';

    public function __construct(
        private readonly ?int $object_id = null
    ) {
        global $DIC;

        $settings = (new SettingsFactory())->getSettings();
        $this->activated = $settings->isPreviewEnabled();

        $this->db = $DIC->database();
        $this->ctrl = $DIC->ctrl();

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->irss = $DIC->resourceStorage();
        $this->http = $DIC->http();
        $this->http_wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->access = $DIC->access();
        $this->language = $DIC->language();
        $this->language->loadLanguageModule('file');
        $this->action_repository = new ActionDBRepository($this->db);

        $rid_string = $this->resolveRidString($this->object_id);

        $this->rid = $this->irss->manage()->find($rid_string);
        $this->flavour_definition = new PagesToExtract(
            $settings->isPersisting(),
            $settings->getImageSize(),
            $settings->getMaximumPreviews()
        );
        $this->fallback_flavour_definition = new FitToSquare(
            $settings->isPersisting(),
            $settings->getImageSize()
        );
        // Resolve File Name
        if ($this->rid !== null) {
            $revision = $this->irss->manage()->getCurrentRevision($this->rid);
            $this->file_name = $revision->getTitle();
            $this->suffix = $revision->getInformation()->getSuffix();
        }
    }

    public function has(): bool
    {
        if (!$this->activated) {
            return false;
        }
        if ($this->rid === null) {
            return false;
        }
        // the viewer of the WOPI client shows the actual file and wins over preview images
        if (!$this->hasWopiPreview() && !$this->hasImagePreview()) {
            return false;
        }
        return $this->isAccessGranted();
    }

    private function hasImagePreview(): bool
    {
        return $this->irss->flavours()->possible($this->rid, $this->flavour_definition)
            || $this->irss->flavours()->possible($this->rid, $this->fallback_flavour_definition);
    }

    /**
     * Files the WOPI client can view are shown with its viewer instead of preview images, as
     * long as the user may see the content of the file - the same permission the content
     * tab of a file object asks for. Everyone else gets the preview images, if there are any.
     */
    private function hasWopiPreview(): bool
    {
        return $this->object_id !== null
            && $this->rid !== null
            && $this->getWopiViewAction() !== null
            && $this->hasPermission((int) $this->object_id, Permissions::VIEW_CONTENT);
    }

    private function getWopiViewAction(): ?Action
    {
        return $this->suffix === '' ? null : $this->action_repository->getViewActionForSuffix($this->suffix);
    }

    private function hasPermission(int $object_id, Permissions $permission): bool
    {
        foreach (ilObject::_getAllReferences($object_id) as $ref_id) {
            if ($this->access->checkAccess($permission->value, '', $ref_id)) {
                return true;
            }
        }
        return false;
    }

    public function getTriggerComponents(bool $as_standard_button = false): array
    {
        if (!$this->isAccessGranted()) {
            throw new LogicException('User cannot see this resource');
        }

        $this->ctrl->setParameterByClass(self::class, self::P_RID, $this->rid->serialize());

        $command = self::CMD_GET_ASYNC_MODAL;
        if ($this->hasWopiPreview()) {
            $command = self::CMD_GET_ASYNC_WOPI_MODAL;
            // the modal is loaded asynchronously, so the script must be part of the page
            global $DIC;
            $DIC->ui()->mainTemplate()->addJavaScript('assets/js/wopi.min.js');
        }

        $modal = $this->ui_factory->modal()
                                  ->lightbox([])
                                  ->withAsyncRenderUrl(
                                      $this->ctrl->getLinkTargetByClass(self::class, $command)
                                  );

        if (!$as_standard_button) {
            $trigger = $this->ui_factory->button()->shy($this->language->txt('show_preview'), "")->withSymbol(
                $this->ui_factory->symbol()->glyph()->preview()
            )->withOnClick($modal->getShowSignal());
        } else {
            $trigger = $this->ui_factory->button()->standard(
                $this->language->txt('show_preview'),
                "#"
            )->withOnClick($modal->getShowSignal());
        }

        return [
            $modal,
            $trigger
        ];
    }

    public function getRenderedTriggerComponents(bool $as_button = false): string
    {
        return $this->ui_renderer->render($this->getTriggerComponents($as_button));
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        match ($cmd) {
            self::CMD_GET_ASYNC_MODAL, self::CMD_GET_ASYNC_WOPI_MODAL => $this->{$cmd}(),
            default => throw new InvalidArgumentException('Command not found: ' . $cmd),
        };
    }

    /**
     * @return mixed
     */
    protected function resolveRidString(?int $object_id): string
    {
        if ($object_id !== null) {
            return $this->db->fetchObject(
                $this->db->queryF(
                    'SELECT rid FROM file_data WHERE file_id = %s',
                    ['integer'],
                    [$object_id]
                )
            )->rid ?? '';
        }
        return $this->http_wrapper->query()->has(self::P_RID)
            ? $this->http_wrapper->query()->retrieve(
                self::P_RID,
                $this->refinery->to()->string()
            ) : '';
    }

    protected function isAccessGranted(): bool
    {
        // if object_id is set, we can check the access using it's ref_ids
        if ($this->object_id !== null) {
            foreach (ilObject::_getAllReferences($this->object_id) as $ref_id) {
                if ($this->access->checkAccess('read', '', $ref_id)) {
                    return true;
                }
            }
            return false;
        }
        // else we ask the stakeholders if they allow access
        $resource = $this->irss->manage()->getResource($this->rid);
        foreach ($resource->getStakeholders() as $stakeholder) {
            if ($stakeholder->canBeAccessedByCurrentUser($this->rid)) {
                return true;
            }
        }
        return false;
    }

    /**
     * The asynchronous request only knows the resource, the file object is looked up by it
     * instead of trusting an object id from the request.
     */
    private function resolveObjectIdForResource(): ?int
    {
        $row = $this->db->fetchObject(
            $this->db->queryF(
                'SELECT file_id FROM file_data WHERE rid = %s',
                ['text'],
                [$this->rid->serialize()]
            )
        );
        return $row === null ? null : (int) $row->file_id;
    }

    private function getAsyncWopiModal(): void
    {
        $object_id = $this->object_id ?? $this->resolveObjectIdForResource();
        $action = $this->getWopiViewAction();
        if (
            $object_id === null
            || $action === null
            || !$this->hasPermission($object_id, Permissions::VIEW_CONTENT)
        ) {
            throw new LogicException('User cannot see this resource');
        }

        $renderer = new WOPIRenderer(
            new EmbeddedApplication(
                $this->rid,
                $action,
                new ilObjFileStakeholder(),
                new URI(ILIAS_HTTP_PATH),
                true,
                $this->language->getLangKey(),
                $this->hasPermission($object_id, Permissions::EDIT_CONTENT)
            )
        );

        $id = 'wopi_preview_' . bin2hex(random_bytes(8));
        $title = sprintf($this->language->txt('preview_caption'), '', $this->file_name);
        $modal = $this->ui_factory->modal()->lightbox(
            $this->ui_factory->modal()->lightboxTextPage(
                $renderer->getPreviewMarkup($id, $title),
                $title
            )
        );

        // Send response and end script
        $response = $this->http->response()->withBody(
            Streams::ofString(
                $this->ui_renderer->renderAsync([$modal, $renderer->getPreviewInitialisation($id)])
            )
        );
        $this->http->saveResponse($response);
        $this->http->sendResponse();
    }

    private function getAsyncModal(): void
    {
        if (!$this->isAccessGranted()) {
            throw new LogicException('User cannot see this resource');
        }

        // Resolve Flavour for Definition
        $flavour = $this->irss->flavours()->get($this->rid, $this->flavour_definition);
        $flavour_urls = $this->irss->consume()->flavourUrls($flavour)->getURLsAsArray();
        if ($flavour_urls === []) { // Try Fallback
            $flavour = $this->irss->flavours()->get($this->rid, $this->fallback_flavour_definition);
            $flavour_urls = $this->irss->consume()->flavourUrls($flavour)->getURLsAsArray();
        }

        $page_title = function (?int $index): string {
            $index_string = $index !== null ? (($index + 1) . ' ') : '';
            return sprintf(
                $this->language->txt('preview_caption'),
                $index_string,
                $this->file_name
            );
        };

        // Build Pages for Lightbox
        $pages = array_map(function (string $url, $i) use ($page_title): LightboxImagePage {
            $title = $page_title($i);
            return $this->ui_factory->modal()->lightboxImagePage(
                $this->ui_factory->image()->responsive(
                    $url,
                    $title
                ),
                $title
            );
        }, $flavour_urls, count($flavour_urls) > 1 ? array_keys($flavour_urls) : []);

        // Fallback to a TextPage if no Flavour Images were found
        if ($pages === []) {
            $pages = $this->ui_factory->modal()->lightboxTextPage(
                sprintf(
                    $this->language->txt('preview_not_possible'),
                    'components/ILIAS/File/classes/Preview/README.md'
                ),
                $this->language->txt('preview')
            );
        }
        $modal = $this->ui_factory->modal()->lightbox($pages);

        // Send response and end script
        $response = $this->http->response()->withBody(Streams::ofString($this->ui_renderer->renderAsync($modal)));
        $this->http->saveResponse($response);
        $this->http->sendResponse();
    }
}
