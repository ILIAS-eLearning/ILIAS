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

namespace ILIAS\Style\Content\Style;

use ILIAS\Style\Content\InternalDataService;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Style\Content\InternalRepoService;
use ILIAS\Style\Content\InternalDomainService;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\FileUpload\DTO\UploadResult;

class StyleManager
{
    protected StyleRepo $repo;
    public function __construct(
        protected InternalDataService $data,
        InternalRepoService $repo,
        protected InternalDomainService $domain,
        protected ResourceStakeholder $stakeholder,
        protected int $style_id
    ) {
        $this->repo = $repo->style();
    }

    public function createRid(): string
    {
        return $this->repo->getOrCreateRid(
            $this->style_id,
            $this->stakeholder
        );
    }

    public function writeCss(): bool
    {
        if ($this->isMigrated()) {
            $builder = $this->domain->cssBuilder(
                new \ilObjStyleSheet($this->style_id)
            );
            $css = $builder->getCss();
            $this->repo->writeCss(
                $this->style_id,
                $css,
                $this->stakeholder
            );
            $this->repo->saveUpToDate(
                $this->style_id,
                true
            );
            return true;
        }
        return false;
    }

    public function getPath(
        bool $add_random = true,
        bool $add_token = true
    ): string {
        return $this->repo->getPath(
            $this->style_id,
            $add_random,
            $add_token
        );
    }

    public function getResourceIdentification(): ?ResourceIdentification
    {
        return $this->repo->getResourceIdentification($this->style_id);
    }

    public function createContainerFromLocalZip(
        string $local_zip_path
    ): string {
        return $this->repo->createContainerFromLocalZip(
            $this->style_id,
            $local_zip_path,
            $this->stakeholder
        );
    }

    public function createContainerFromLocalDir(
        string $local_dir_path,
        string $container_path = "",
        bool $recursive = true
    ): string {
        return $this->repo->createContainerFromLocalDir(
            $this->style_id,
            $local_dir_path,
            $this->stakeholder,
            $container_path,
            $recursive
        );
    }

    public function importFromUploadResult(
        UploadResult $result,
    ): int {
        $imp = new \ilImport();
        return $imp->importObject(
            null,
            $result->getPath(),
            $result->getName(),
            "sty",
            "",
            true
        );
    }

    public function cloneResourceContainer(
        int $from_style_id
    ): void {
        $this->repo->cloneResourceContainer(
            $from_style_id,
            $this->style_id
        );
    }

    public function isMigrated(): bool
    {
        return ((string) $this->repo->getResourceIdentification($this->style_id)
            !== "");
    }

    public function migrateImages(
    ): void {
        $this->repo->migrateImages($this->style_id);
    }

}
