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

use ILIAS\MediaObjects\InternalDomainService;
use ILIAS\MediaObjects\InternalGUIService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaObjectsPlayerWrapperGUI
{
    protected \ILIAS\MediaObjects\Tracking\TrackingManager $tracking;
    protected \ILIAS\MediaObjects\Player\PlayerGUIRequest $request;
    protected \ILIAS\MediaObjects\MediaType\MediaTypeManager $media_type;
    protected InternalGUIService $gui;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        $this->gui = $gui_service;
        $this->domain = $domain_service;
        $this->media_type = $this->domain->mediaType();
        $this->request = $gui_service->player()->request();
        $this->tracking = $this->domain->tracking();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd();

        switch ($next_class) {
            default:
                if (in_array($cmd, array("saveCompletion"))) {
                    $this->$cmd();
                }
        }
    }

    public function renderComponent(
        ilObjMediaObject $mob,
        int $tracking_container_ref_id = 0
    ): string {
        $comp = $this->getComponent($mob, $tracking_container_ref_id);
        if ($comp) {
            return $this->gui->ui()->renderer()->render($comp);
        }
        return "";
    }
    public function getComponent(
        ilObjMediaObject $mob,
        int $tracking_container_ref_id = 0
    ): ILIAS\UI\Component\Component {
        $med = $mob->getMediaItem("Standard");
        $comp = null;
        if (!is_null($med)) {
            if ($this->media_type->isAudio($med->getFormat())) {
                $comp = $this->audio(
                    $mob,
                    $tracking_container_ref_id
                );
            } elseif ($this->media_type->isVideo($med->getFormat())) {
                $comp = $this->video(
                    $mob,
                    $tracking_container_ref_id
                );
            } elseif ($this->media_type->isImage($med->getFormat())) {
                $comp = $this->image(
                    $mob,
                    $tracking_container_ref_id
                );
            }
        }
        return $comp;
    }

    /**
     * @throws ilCtrlException
     */
    public function audio(
        ilObjMediaObject $mob,
        int $tracking_container_ref_id = 0
    ): ?\ILIAS\UI\Component\Player\Audio {
        $main_tpl = $this->gui->ui()->mainTemplate();

        $ctrl = $this->gui->ctrl();

        $med = $mob->getMediaItem("Standard");

        if (is_null($med) || !$this->media_type->isAudio($med->getFormat())) {
            return null;
        }
        $resource = $mob->getStandardSrc();
        $audio = $this->gui->ui()->factory()->player()->audio(
            $resource,
            ""
        );

        if ($tracking_container_ref_id > 0) {
            // @todo: make this a media object general setting
            $mcst_settings = ilMediaCastSettings::_getInstance();
            $treshold = (int) $mcst_settings->getVideoCompletionThreshold();

            $main_tpl->addJavaScript("assets/js/MediaObjectsCompletion.js");
            $ctrl->setParameter($this, "mob_tracking_ref_id", $tracking_container_ref_id);
            $ctrl->setParameter($this, "mob_tracking_mob_id", $mob->getId());
            $url = $ctrl->getLinkTarget($this, "saveCompletion");
            $audio = $audio->withAdditionalOnLoadCode(function ($id) use ($url, $treshold) {
                $js = <<<EOT
                document.getElementById('$id').dataset.mobCompletionCallback = '$url';
                document.getElementById('$id').dataset.mobCompletionThreshold = '$treshold';
                il.MediaObjectsCompletion.init();
EOT;
                return $js;
            });
        }
        return $audio;
    }

    /**
     * @throws ilCtrlException
     */
    public function image(
        ilObjMediaObject $mob,
        int $tracking_container_ref_id = 0
    ): ?\ILIAS\UI\Component\Image\Image {
        $main_tpl = $this->gui->ui()->mainTemplate();

        $ctrl = $this->gui->ctrl();

        $med = $mob->getMediaItem("Standard");

        if (is_null($med) || !$this->media_type->isImage($med->getFormat())) {
            return null;
        }

        $source = $mob->getStandardSrc();

        $image = $this->gui->ui()->factory()->image()->responsive($source, $mob->getTitle());

        if ($tracking_container_ref_id > 0) {
            // @todo: make this a media object general setting
            $mcst_settings = ilMediaCastSettings::_getInstance();
            $treshold = (int) $mcst_settings->getVideoCompletionThreshold();

            $main_tpl->addJavaScript("assets/js/MediaObjectsCompletion.js");
            $ctrl->setParameter($this, "mob_tracking_ref_id", $tracking_container_ref_id);
            $ctrl->setParameter($this, "mob_tracking_mob_id", $mob->getId());
            $url = $ctrl->getLinkTarget($this, "saveCompletion");
            $audio = $image->withAdditionalOnLoadCode(function ($id) use ($url, $treshold) {
                $js = <<<EOT
                document.getElementById('$id').dataset.mobCompletionCallback = '$url';
                document.getElementById('$id').dataset.mobCompletionThreshold = '$treshold';
                il.MediaObjectsCompletion.init();
EOT;
                return $js;
            });
        }
        return $image;
    }

    /**
     * @throws ilCtrlException
     */
    public function video(
        ilObjMediaObject $mob,
        int $tracking_container_ref_id = 0
    ): ?\ILIAS\UI\Component\Player\Video {
        $main_tpl = $this->gui->ui()->mainTemplate();

        $ctrl = $this->gui->ctrl();

        $med = $mob->getMediaItem("Standard");

        if (is_null($med) || !$this->media_type->isVideo($med->getFormat())) {
            return null;
        }

        $source = $mob->getStandardSrc();

        $video = $this->gui->ui()->factory()->player()->video(
            $source,
            ""
        );

        if ($tracking_container_ref_id > 0) {
            // @todo: make this a media object general setting
            $mcst_settings = ilMediaCastSettings::_getInstance();
            $treshold = (int) $mcst_settings->getVideoCompletionThreshold();

            $main_tpl->addJavaScript("assets/js/MediaObjectsCompletion.js");
            $ctrl->setParameter($this, "mob_tracking_ref_id", $tracking_container_ref_id);
            $ctrl->setParameter($this, "mob_tracking_mob_id", $mob->getId());
            $url = $ctrl->getLinkTarget($this, "saveCompletion");
            $audio = $video->withAdditionalOnLoadCode(function ($id) use ($url, $treshold) {
                $js = <<<EOT
                document.getElementById('$id').dataset.mobCompletionCallback = '$url';
                document.getElementById('$id').dataset.mobCompletionThreshold = '$treshold';
                il.MediaObjectsCompletion.init();
EOT;
                return $js;
            });
        }
        return $video;
    }

    protected function saveCompletion(): void
    {
        $ref_id = $this->request->getTrackingRefId();
        $mob_id = $this->request->getTrackingMobId();
        $this->tracking->saveCompletion($mob_id, $ref_id);
    }
}
