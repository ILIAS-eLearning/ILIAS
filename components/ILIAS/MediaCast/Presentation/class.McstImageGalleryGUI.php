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

/**
 * Image gallery GUI for mediacasts
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class McstImageGalleryGUI
{
    protected \ILIAS\MediaObjects\MediaType\MediaTypeManager $media_types;
    protected \ILIAS\MediaCast\InternalGUIService $gui;
    protected \ILIAS\MediaCast\MediaCastManager $mc_manager;
    protected string $rss_link;
    protected \ilObjMediaCast $media_cast;
    protected ilGlobalTemplateInterface $tpl;
    protected \ILIAS\DI\UIServices $ui;
    protected \ilLanguage $lng;
    protected \ilObjUser $user;
    protected \ilCtrl $ctrl;
    protected \ilToolbarGUI $toolbar;

    public function __construct(
        \ilObjMediaCast $obj,
        $tpl = null,
        string $rss_link = ""
    ) {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->rss_link = $rss_link;
        $this->lng = $DIC->language();
        $this->media_cast = $obj;
        $this->tpl = $tpl;
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->toolbar = $DIC->toolbar();
        $this->media_types = $DIC->mediaObjects()->internal()->domain()->mediaType();
        $this->mc_manager = $DIC->mediaCast()->internal()->domain()->mediaCast($this->media_cast);
        $this->gui = $DIC->mediaCast()->internal()->gui();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd();

        switch ($next_class) {
            default:
                if (in_array($cmd, array("downloadAll"))) {
                    $this->$cmd();
                }
        }
    }

    public function getHTML(): string
    {
        $f = $this->ui->factory();
        $renderer = $this->ui->renderer();
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $toolbar = $this->toolbar;

        // toolbar
        $toolbar->setFormAction($ctrl->getFormAction($this));
        if ($this->media_cast->getDownloadable()) {
            $toolbar->addFormButton($lng->txt("mcst_download_all"), "downloadAll");
        }

        if ($this->rss_link !== "") {
            $b = $f->link()->standard(
                $lng->txt("mcst_webfeed"),
                $this->rss_link
            )->withOpenInNewViewport(true);
            $toolbar->addComponent($b);
        }

        // cards and modals
        $cards = [];
        $modals = [];

        $pages = [];
        foreach ($this->media_cast->getSortedItemsArray() as $item) {
            $mob = new \ilObjMediaObject($item["mob_id"]);
            $med = $mob->getMediaItem("Standard");
            if (!in_array($med->getFormat(), iterator_to_array($this->media_types->getAllowedImageMimeTypes()), true)) {
                continue;
            }

            if (strcasecmp("Reference", $med->getLocationType()) == 0) {
                $resource = $med->getLocation();
            } else {
                $path_to_file = \ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
                $resource = ilWACSignedPath::signFile($path_to_file);
            }

            $image = $f->image()->responsive(
                $resource,
                $mob->getTitle()
            );

            $pages[] = $f->modal()->lightboxImagePage($image, $mob->getTitle());
        }
        $main_modal = $f->modal()->lightbox($pages);

        $cnt = 0;
        foreach ($this->media_cast->getSortedItemsArray() as $item) {
            $mob = new \ilObjMediaObject($item["mob_id"]);
            $med = $mob->getMediaItem("Standard");

            if (!in_array($med->getFormat(), iterator_to_array($this->media_types->getAllowedImageMimeTypes()), true)) {
                continue;
            }

            if (strcasecmp("Reference", $med->getLocationType()) == 0) {
                $resource = $med->getLocation();
            } else {
                $path_to_file = \ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
                $resource = ilWACSignedPath::signFile($path_to_file);
            }
            $preview_resource = $resource;
            if ($mob->getVideoPreviewPic() != "") {
                $preview_resource = ilWACSignedPath::signFile($mob->getVideoPreviewPic());
            }


            $preview_image = $f->image()->responsive(
                $preview_resource,
                $mob->getTitle()
            );

            $image = $f->image()->responsive(
                $resource,
                $mob->getTitle()
            );

            $modal = $main_modal;

            $card_image = $preview_image->withAction($modal->getShowSignal());
            $card_image = $card_image->withAdditionalOnLoadCode(function ($id) use ($cnt) {
                return "$('#$id').click(function(e) { document.querySelector('.modal-body .carousel [data-slide-to=\"" . $cnt . "\"]').click(); });";
            });
            $cnt++;

            $sections = ($mob->getDescription())
                ? [$f->legacy($mob->getDescription())]
                : [];

            if ($this->media_cast->getDownloadable()) {
                $ctrl->setParameterByClass("ilobjmediacastgui", "item_id", $item["id"]);
                $ctrl->setParameterByClass("ilobjmediacastgui", "purpose", "Standard");
                $download = $ctrl->getLinkTargetByClass("ilobjmediacastgui", "downloadItem");
                $sections[] = $f->button()->standard($lng->txt("download"), $download);
            }

            // comments
            if ($this->mc_manager->commentsActive()) {
                $comments_gui = $this->gui->comments()->commentGUI(
                    $this->media_cast->getRefId(),
                    (int) $item["id"]
                );
                $sections[] = $f->legacy($comments_gui->getGlyph());
            }

            //$title_button = $f->button()->shy($mob->getTitle(), $modal->getShowSignal());
            $title = $mob->getTitle();

            $card = $f->card()->standard(
                $title,
                $card_image
            )->withSections(
                $sections
            )->withTitleAction($modal->getShowSignal());

            $cards[] = $card;
            $modals[] = $modal;
        }

        $deck = $f->deck($cards);

        if (count($pages) == 0) {
            return "";
        }
        return "<div id='il-mcst-img-gallery'>" . $renderer->render(array_merge([$deck], [$main_modal])) . "</div>";
    }

    protected function downloadAll(): void
    {
        $user = $this->user;
        $download_task = new \ILIAS\MediaCast\BackgroundTasks\DownloadAllBackgroundTask(
            (int) $user->getId(),
            (int) $this->media_cast->getRefId(),
            (int) $this->media_cast->getId()
        );

        if ($download_task->run()) {
            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt('mcst_download_started_bg'),
                true
            );
        }

        $this->ctrl->redirectByClass("ilobjmediacastgui", "showContent");
    }
}
