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

namespace ILIAS\MediaObjects\Video;

use ILIAS\MediaObjects\InternalGUIService;
use ILIAS\MediaObjects\InternalDomainService;

class GUIService
{
    protected \ilGlobalTemplateInterface $tpl;
    protected InternalGUIService $gui_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        $this->gui_service = $gui_service;
        $this->domain_service = $domain_service;
        $this->tpl = $gui_service->ui()->mainTemplate();
        $lng = $this->domain_service->lng();
        $lng->loadLanguageModule("mob");
    }

    public function addPreviewExtractionToToolbar(
        int $mob_id,
        string $gui_class,
        string $extract_cmd = "extractPreviewImage"
    ): void {
        $toolbar = $this->gui_service->toolbar();
        $ctrl = $this->gui_service->ctrl();
        $lng = $this->domain_service->lng();

        if (\ilFFmpeg::enabled()) {
            $mob = new \ilObjMediaObject($mob_id);

            $conv_cnt = 0;
            // we had other purposes as source as well, but
            // currently only "Standard" is implemented in the convertFile method
            $p = "Standard";
            $med = $mob->getMediaItem($p);
            if (is_object($med)) {
                if (\ilFFmpeg::supportsImageExtraction($med->getFormat())) {
                    // second
                    $ni = new \ilTextInputGUI($lng->txt("mob_second"), "sec");
                    $ni->setMaxLength(4);
                    $ni->setSize(4);
                    $ni->setValue(1);
                    $toolbar->addInputItem($ni, true);

                    $toolbar->addFormButton($lng->txt("mob_extract_preview_image"), "extractPreviewImage");
                    $toolbar->setFormAction($ctrl->getFormActionByClass($gui_class));
                }
            }
        }
    }

    public function handleExtractionRequest(
        int $mob_id
    ): void {
        $mob = new \ilObjMediaObject($mob_id);
        $lng = $this->domain_service->lng();
        $add = "";
        try {
            $sec = $this->gui_service->standardRequest()->getSeconds();
            if ($sec < 0) {
                $sec = 0;
            }

            $mob->generatePreviewPic(320, 240, $sec);
            if ($mob->getVideoPreviewPic() !== "") {
                $this->tpl->setOnScreenMessage('info', $lng->txt("mob_image_extracted"), true);
            } else {
                $this->tpl->setOnScreenMessage('failure', $lng->txt("mob_no_extraction_possible"), true);
            }
        } catch (\ilException $e) {
            if (DEVMODE === 1) {
                $ret = \ilFFmpeg::getLastReturnValues();
                $add = (is_array($ret) && count($ret) > 0)
                    ? "<br />" . implode("<br />", $ret)
                    : "";
            }
            $this->tpl->setOnScreenMessage('failure', $e->getMessage() . $add, true);
        }
    }

    protected function checkPreviewPossible(int $mob_id): bool
    {
        if ($mob_id === 0) {
            return false;
        }
        $mob = new \ilObjMediaObject($mob_id);
        $med = $mob->getMediaItem("Standard");
        if (is_object($med)) {
            if (\ilFFmpeg::supportsImageExtraction($med->getFormat())) {
                return true;
            }
        }
        return false;
    }

    public function addPreviewInput(\ilPropertyFormGUI $form, int $mob_id = 0): void
    {
        if (!$this->checkPreviewPossible($mob_id)) {
            return;
        }
        $lng = $this->domain_service->lng();
        $pp = new \ilImageFileInputGUI($lng->txt("mob_preview_picture"), "preview_pic");
        $pp->setSuffixes(array("png", "jpeg", "jpg"));
        $form->addItem($pp);

        if ($mob_id > 0) {
            $mob = new \ilObjMediaObject($mob_id);
            // preview
            $ppic = $mob->getVideoPreviewPic();
            if ($ppic !== "") {
                $pp->setImage($ppic . "?rand=" . rand(0, 1000));
            }
        }
    }

    public function savePreviewInput(\ilPropertyFormGUI $form, int $mob_id): void
    {
        if (!$this->checkPreviewPossible($mob_id)) {
            return;
        }
        $prevpic = $form->getInput("preview_pic");
        if ($prevpic["size"] > 0) {
            $mob = new \ilObjMediaObject($mob_id);
            $mob->uploadVideoPreviewPic($prevpic);
        } else {
            $prevpici = $form->getItemByPostVar("preview_pic");
            if ($prevpici->getDeletionFlag()) {
                $mob = new \ilObjMediaObject($mob_id);
                $mob->removeAdditionalFile($mob->getVideoPreviewPic(true));
            }
        }

    }
}
