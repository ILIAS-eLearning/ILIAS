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
use ilWACSignedPath;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use Exception;

class ilBadgeImage
{
    public const IMAGE_SIZE_XS = 4;
    public const IMAGE_SIZE_S = 3;
    public const IMAGE_SIZE_M = 2;
    public const IMAGE_SIZE_L = 1;
    public const IMAGE_SIZE_XL = 0;
    public const IMAGE_URL_COUNT = 5;

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

    public function getImageFromBadge(ilBadge $badge, int $size = self::IMAGE_SIZE_XS): string
    {
        return $this->getImageFromResourceId($badge, $size);
    }

    public function getImageFromResourceId(
        ilBadge $badge,
        int $size = self::IMAGE_SIZE_XS
    ): string {
        $image_src = '';

        if ($badge->getImageRid() !== '' && $badge->getImageRid() !== null) {
            $identification = $this->resource_storage->manage()->find($badge->getImageRid());
            if ($identification !== null) {
                $flavour = $this->resource_storage->flavours()->get($identification, new \ilBadgePictureDefinition());
                $urls = $this->resource_storage->consume()->flavourUrls($flavour)->getURLsAsArray();
                if (\count($urls) === self::IMAGE_URL_COUNT && isset($urls[$size])) {
                    $image_src = $urls[$size];
                }
            }
        } elseif ($badge instanceof ilBadge) {
            $image_src = ilWACSignedPath::signFile($badge->getImagePath());
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
            $badge->setImageRid($identification->serialize());
            $badge->update();
        } catch (IllegalStateException $e) {
            $this->main_template->setOnScreenMessage('failure', $e->getMessage(), true);
        }
    }

    /**
     * @throws Exception
     */
    public function cloneBadgeImageByRid(ResourceIdentification $identification): string
    {
        return $this->resource_storage->manage()->clone($identification)->serialize();
    }
}
