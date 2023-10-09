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

namespace ILIAS\COPage\PC\InteractiveImage;

use ILIAS\COPage\InternalDomainService;
use ILIAS\FileUpload\Location;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\HandlerResult;

class IIMManager
{
    protected \ilLogger $log;
    protected InternalDomainService $domain;
    protected \ILIAS\COPage\Dom\DomUtil $dom_util;

    public function __construct(
        InternalDomainService $domain
    ) {
        global $DIC;
        $this->domain = $domain;
        $this->dom_util = $DIC->copage()->internal()->domain()->domUtil();
        if (isset($DIC['ilLoggerFactory'])) {
            $this->log = $domain->log();
        }
    }

    public function handleUploadResult(
        FileUpload $upload,
        UploadResult $result,
        \ilObjMediaObject $mob = null
    ): BasicHandlerResult {
        $this->log->debug("Handle mob upload");
        $title = $result->getName();
        $this->log->debug($title);

        if (is_null($mob)) {
            $this->log->debug("New...");
            $mob = new \ilObjMediaObject();
            $mob->setTitle($title);
            $mob->setDescription("");
            $mob->create();

            $mob->createDirectory();
            $media_item = new \ilMediaItem();
            $mob->addMediaItem($media_item);
            $media_item->setPurpose("Standard");
        } else {
            $this->log->debug("Update...");
            $media_item = $mob->getMediaItem("Standard");
        }

        $mob_dir = \ilObjMediaObject::_getRelativeDirectory($mob->getId());
        $file_name = \ilObjMediaObject::fixFilename($title);
        $file = $mob_dir . "/" . $file_name;

        $this->log->debug("Move file to: " . $mob_dir . ", " . $file_name);
        $upload->moveOneFileTo(
            $result,
            $mob_dir,
            Location::WEB,
            $file_name,
            true
        );

        // get mime type
        $format = \ilObjMediaObject::getMimeType($file);
        $location = $file_name;

        // set real meta and object data
        $media_item->setFormat($format);
        $media_item->setLocation($location);
        $media_item->setLocationType("LocalFile");
        $mob->update();

        return new BasicHandlerResult(
            "mob_id",
            HandlerResult::STATUS_OK,
            (string) $mob->getId(),
            ''
        );
    }

    public function handleOverlayUpload(
        \ilObjMediaObject $mob,
        FileUpload $upload,
        UploadResult $result
    ): BasicHandlerResult {
        $mob->addAdditionalFileFromUpload(
            $upload,
            $result,
            "overlays"
        );
        $mob->makeThumbnail(
            "overlays/" . $result->getName(),
            $this->getOverlayThumbnailName($result->getName())
        );
        return new BasicHandlerResult(
            "mob_id",
            HandlerResult::STATUS_OK,
            (string) $mob->getId(),
            ''
        );
    }

    public function getOverlayWebPath(\ilObjMediaObject $mob, string $file): string
    {
        return \ilObjMediaObject::_getURL($mob->getId()) . "/overlays/" . $file;
    }

    public function getOverlayThumbnailPath(\ilObjMediaObject $mob, string $file): string
    {
        return \ilObjMediaObject::getThumbnailPath(
            $mob->getId(),
            $this->getOverlayThumbnailName($file)
        );
    }

    protected function getOverlayThumbnailName(string $file): string
    {
        $piname = pathinfo($file);
        return basename($file, "." . $piname['extension']) . ".png";
    }

    public function getOverlays(\ilObjMediaObject $mob): array
    {
        return array_map(
            function ($file) use ($mob) {
                return [
                    "name" => $file,
                    "thumbpath" => $this->getOverlayThumbnailPath($mob, $file),
                    "webpath" => $this->getOverlayWebPath($mob, $file)
                ];
            },
            $mob->getFilesOfDirectory("overlays")
        );
    }

    /**
     * Resolve iim media aliases
     * (in ilContObjParse)
     */
    public function resolveIIMMediaAliases(
        \DOMDocument $dom,
        array $a_mapping
    ): bool {
        // resolve normal internal links
        $path = "//InteractiveImage/MediaAlias";
        $changed = false;
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $old_id = $node->getAttribute("OriginId");
            if ($a_mapping[$old_id] > 0) {
                $node->setAttribute("OriginId", "il__mob_" . $a_mapping[$old_id]);
                $changed = true;
            }
        }
        return $changed;
    }
}
