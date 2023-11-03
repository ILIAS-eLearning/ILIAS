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

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\UI\Component\Modal\LightboxImagePage;
use ILIAS\Modules\File\Preview\Settings;
use ILIAS\ResourceStorage\Flavour\Definition\CropToSquare;
use ILIAS\ResourceStorage\Flavour\Definition\FitToSquare;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilObjFilePreviewRendererGUI implements ilCtrlBaseClassInterface
{
    public const P_RID = "rid";
    public const CMD_GET_ASYNC_MODAL = 'getAsyncModal';

    private ilDBInterface $db;
    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\UI\Renderer $ui_renderer;
    private ilCtrlInterface $ctrl;
    private ?ResourceIdentification $rid = null;
    private \ILIAS\ResourceStorage\Services $irss;
    private \ILIAS\HTTP\Services $http;
    private \ILIAS\HTTP\Wrapper\WrapperFactory $http_wrapper;
    private \ILIAS\Refinery\Factory $refinery;
    private FlavourDefinition $flavour_definition;
    private ilAccessHandler $access;
    private ilLanguage $language;
    private int $preview_size;
    private int $pages_to_extract;
    private bool $activated = false;
    private string $file_name = '';
    private FlavourDefinition $fallback_flavour_definition;

    public function __construct(
        private ?int $object_id = null
    ) {
        global $DIC;

        $settings = new Settings();
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
            $this->file_name = $this->irss->manage()->getCurrentRevision($this->rid)->getTitle();
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
        if (
            !$this->irss->flavours()->possible(
                $this->rid,
                $this->flavour_definition
            )
            && !$this->irss->flavours()->possible(
                $this->rid,
                $this->fallback_flavour_definition
            )
        ) {
            return false;
        }
        return $this->isAccessGranted();
    }

    public function getTriggerComponents(bool $as_button = false): array
    {
        if (!$this->isAccessGranted()) {
            throw new LogicException('User cannot see this resource');
        }

        $this->ctrl->setParameterByClass(self::class, self::P_RID, $this->rid->serialize());

        $modal = $this->ui_factory->modal()
                                  ->lightbox([])
                                  ->withAsyncRenderUrl(
                                      $this->ctrl->getLinkTargetByClass(self::class, self::CMD_GET_ASYNC_MODAL)
                                  );

        if (!$as_button) {
            $trigger = $this->ui_factory->symbol()->glyph()->eyeopen(
                "#"
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
            self::CMD_GET_ASYNC_MODAL => $this->{$cmd}(),
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
                    'Modules/File/classes/Preview/README.md'
                ),
                $this->language->txt('preview')
            );
        }
        $modal = $this->ui_factory->modal()->lightbox($pages);

        // Send response and end script
        $response = $this->http->response()->withBody(Streams::ofString($this->ui_renderer->render($modal)));
        $this->http->saveResponse($response);
        $this->http->sendResponse();
    }
}
