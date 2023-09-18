<?php

declare(strict_types=1);

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

namespace ILIAS\MediaObjects\ImageMap;

/**
 * Manages items in repository clipboard
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ImageMapManager
{
    protected ImageMapEditSessionRepository $repo;

    public function __construct(ImageMapEditSessionRepository $repo)
    {
        $this->repo = $repo;
    }

    public function setTargetScript(string $script): void
    {
        $this->repo->setTargetScript($script);
    }

    public function getTargetScript(): string
    {
        return $this->repo->getTargetScript();
    }

    public function setRefId(int $ref_id): void
    {
        $this->repo->setRefId($ref_id);
    }

    public function getRefId(): int
    {
        return $this->repo->getRefId();
    }

    public function setObjId(int $obj_id): void
    {
        $this->repo->setObjId($obj_id);
    }

    public function getObjId(): int
    {
        return $this->repo->getObjId();
    }

    public function setHierId(string $hier_id): void
    {
        $this->repo->setHierId($hier_id);
    }

    public function getHierId(): string
    {
        return $this->repo->getHierId();
    }

    public function setPCId(string $pc_id): void
    {
        $this->repo->setPCId($pc_id);
    }

    public function getPCId(): string
    {
        return $this->repo->getPCId();
    }

    public function setAreaType(string $type): void
    {
        $this->repo->setAreaType($type);
    }

    public function getAreaType(): string
    {
        return $this->repo->getAreaType();
    }

    public function setAreaNr(int $nr): void
    {
        $this->repo->setAreaNr($nr);
    }

    public function getAreaNr(): int
    {
        return $this->repo->getAreaNr();
    }

    public function setCoords(string $coords): void
    {
        $this->repo->setCoords($coords);
    }

    public function getCoords(): string
    {
        return $this->repo->getCoords();
    }

    public function setMode(string $mode): void
    {
        $this->repo->setMode($mode);
    }

    public function getMode(): string
    {
        return $this->repo->getMode();
    }

    public function setLinkType(string $type): void
    {
        $this->repo->setLinkType($type);
    }

    public function getLinkType(): string
    {
        return $this->repo->getLinkType();
    }

    public function setExternalLink(string $href): void
    {
        $this->repo->setExternalLink($href);
    }

    public function getExternalLink(): string
    {
        return $this->repo->getExternalLink();
    }

    public function setInternalLink(
        string $type,
        string $target,
        string $target_frame,
        string $anchor
    ): void {
        $this->repo->setInternalLink(
            $type,
            $target,
            $target_frame,
            $anchor
        );
    }

    public function getInternalLink(): array
    {
        return $this->repo->getInternalLink();
    }

    public function clear(): void
    {
        $this->repo->clear();
    }
}
