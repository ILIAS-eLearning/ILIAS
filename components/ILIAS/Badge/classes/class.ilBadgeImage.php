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

namespace ILIAS\Badge;

use ilBadge;
use ILIAS\ResourceStorage\Services;
use ilBadgeFileStakeholder;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Exception\IllegalStateException;
use ilGlobalTemplateInterface;

class ilBadgeImage
{
    private ?Services $resource_storage;
    private ?FileUpload $upload_service;
    private ?ilGlobalTemplateInterface $main_template;

    public function __construct(
        Services $resourceStorage,
        FileUpload $uploadService,
        ilGlobalTemplateInterface $main_template
    ) {
        $this->resource_storage = $resourceStorage;
        $this->upload_service = $uploadService;
        $this->main_template = $main_template;
    }

    public function getImageFromBadge(ilBadge $badge): string
    {
        $image_rid = $badge->getImageRid();
        return $this->getImageFromResourceId($badge, $image_rid);
    }

    public function getImageFromResourceId(ilBadge|array $badge, ?string $image_rid, $size = 4): string
    {
        $image_src = '';

        if ($image_rid !== null) {
            $identification = $this->resource_storage->manage()->find($image_rid);
            if ($identification !== null) {
                $flavour = $this->resource_storage->flavours()->get($identification, new \ilBadgePictureDefinition());
                $urls = $this->resource_storage->consume()->flavourUrls($flavour)->getURLsAsArray(false);
                if (sizeof($urls) === 5 && isset($urls[$size])) {
                    $image_src = $urls[$size];
                }
            }
        } elseif (is_array($badge) && isset($badge['image'])) {
            $image_src = $badge['image'];
        } else {
            $image_src = $badge->getImage();
        }

        return $image_src;
    }

    public function processImageUpload(ilBadge $badge): void
    {
        try {
            $array_result = $this->upload_service->getResults();
            $array_result = array_pop($array_result);
            $stakeholder = new ilBadgeFileStakeholder();
            $identification = $this->resource_storage->manage()->upload($array_result, $stakeholder);
            $this->resource_storage->flavours()->ensure($identification, new \ilBadgePictureDefinition());
            $badge->setImageRid((string) $identification);
            $badge->update();
        } catch (IllegalStateException $e) {
            $this->main_template->setOnScreenMessage('failure', $e->getMessage(), true);
        }
    }
}
