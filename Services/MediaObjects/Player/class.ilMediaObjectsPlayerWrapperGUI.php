<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

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

    public function executeCommand() : void
    {
        $ctrl = $this->gui->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("saveCompletion"))) {
                    $this->$cmd();
                }
        }
    }

    /**
     * @throws ilCtrlException
     */
    public function audio(
        ilObjMediaObject $mob,
        int $tracking_container_ref_id = 0
    ) : ?\ILIAS\UI\Component\Player\Audio
    {
        $main_tpl = $this->gui->ui()->mainTemplate();

        $ctrl = $this->gui->ctrl();

        $med = $mob->getMediaItem("Standard");

        if (is_null($med) || !$this->media_type->isAudio($med->getFormat())) {
            return null;
        }

        if ($med->getLocationType() === "Reference") {
            $resource = $med->getLocation();
        } else {
            $path_to_file = \ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
            $resource = $path_to_file;
        }

        $audio = $this->gui->ui()->factory()->player()->audio(
            $resource,
            ""
        );

        if ($tracking_container_ref_id > 0) {
            // @todo: make this a media object general setting
            $mcst_settings = ilMediaCastSettings::_getInstance();
            $treshold = (int) $mcst_settings->getVideoCompletionThreshold();

            $main_tpl->addJavaScript("./Services/MediaObjects/js/MediaObjectsCompletion.js");
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

    protected function saveCompletion() : void
    {
        $ref_id = $this->request->getTrackingRefId();
        $mob_id = $this->request->getTrackingMobId();
        $this->tracking->saveCompletion($mob_id, $ref_id);
    }
}