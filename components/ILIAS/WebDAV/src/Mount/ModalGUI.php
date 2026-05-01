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

namespace ILIAS\WebDAV\Mount;

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Modal\Lightbox;
use ilLanguage;
use InvalidArgumentException;

class ModalGUI
{
    private const string MOUNT_INSTRUCTIONS_CONTENT_ID = 'webdav_mount_instructions_content';
    private static bool $modal_already_rendered = false;

    private Lightbox $modal;

    private function __construct(
        protected Repository $repository,
        protected Factory $ui_factory,
        protected Renderer $ui_renderer,
        protected ilLanguage $lng
    ) {
        try {
            $document = $this->repository->getMountInstructionsByLanguage($this->lng->getUserLanguage());
            $title = $document->getTitle();
        } catch (InvalidArgumentException) {
            $title = $this->lng->txt('webfolder_instructions_titletext');
        }

        $content_div = '<div id="' . self::MOUNT_INSTRUCTIONS_CONTENT_ID . '"></div>';
        $page = $this->ui_factory->modal()->lightboxTextPage($content_div, $title);
        $this->modal = $this->ui_factory->modal()->lightbox($page);
    }

    private function getRenderedModal(): string
    {
        return $this->ui_renderer->render($this->modal);
    }

    private function getModalShowSignalId(): string
    {
        return $this->modal->getShowSignal()->getId();
    }

    public static function maybeRenderWebDAVModalInGlobalTpl(): void
    {
        if (self::$modal_already_rendered) {
            return;
        }

        global $DIC;
        $repository = new RepositoryDB($DIC->database());
        $instance = new self(
            $repository,
            $DIC->ui()->factory(),
            $DIC->ui()->renderer(),
            $DIC->language()
        );

        self::$modal_already_rendered = true;
        $js_function = '<script>function triggerWebDAVModal(api_url){ $.ajax(api_url).done(function(data){ $(document).trigger("'
            . $instance->getModalShowSignalId()
            . '", "{}"); $("#' . self::MOUNT_INSTRUCTIONS_CONTENT_ID . '").html(data);}) }</script>';

        $webdav_modal_html = $instance->getRenderedModal() . $js_function;

        $tpl = $DIC->ui()->mainTemplate();
        $tpl->setVariable('WEBDAV_MODAL', $webdav_modal_html);
    }
}
