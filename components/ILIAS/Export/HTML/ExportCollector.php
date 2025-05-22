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

namespace ILIAS\components\Export\HTML;

use ILIAS\Export\HTML\ExportFileDBRepository;
use ILIAS\Export\HTML\DataService;

class ExportCollector
{
    protected string $rid = "";

    public function __construct(
        protected DataService $data,
        protected ExportFileDBRepository $repo,
        protected int $obj_id,
        protected string $type = ""
    ) {
    }

    /**
     * @throws ExportException
     */
    public function init(string $zipname = ""): string
    {
        if ($this->rid !== "") {
            throw $this->data->exportException("HTML Export has been initialised twice.");
        }
        if ($zipname === "") {
            $date = time();
            $zipname = $date . "__" . IL_INST_ID . "__" .
                \ilObject::_lookupType($this->obj_id) . "_" . $this->obj_id . ".zip";
        }
        $this->rid = $this->repo->create(
            $this->obj_id,
            $this->type,
            $zipname
        );

        //$this->repo->rename($this->rid, $zipname);

        return $this->rid;
    }

    /**
     * @throws ExportException
     */
    public function addString(
        string $content,
        string $path
    ): void {
        if ($this->rid === "") {
            throw $this->data->exportException("HTML Export has not been initialised.");
        }
        $this->repo->addString(
            $this->rid,
            $content,
            $path
        );
    }

    /**
     * @throws ExportException
     */
    public function addFile(
        string $fullpath,
        string $target_path
    ): void {
        if ($this->rid === "") {
            throw $this->data->exportException("HTML Export has not been initialised.");
        }
        $this->repo->addFile(
            $this->rid,
            $fullpath,
            $target_path
        );
    }

    /**
     * @throws ExportException
     */
    public function addDirectory(
        string $source_dir,
        string $target_path
    ): void {
        if ($this->rid === "") {
            throw $this->data->exportException("HTML Export has not been initialised.");
        }
        $this->repo->addDirectory(
            $this->rid,
            $source_dir,
            $target_path
        );
    }

    public function addContainerDirectory(
        string $source_container_id,
        string $source_dir_path = "",
        string $target_dir_path = ""
    ): void {
        if ($this->rid === "") {
            throw $this->data->exportException("HTML Export has not been initialised.");
        }
        $this->repo->addContainerDirToTargetContainer(
            $source_container_id,
            $this->rid,
            $source_dir_path,
            $target_dir_path
        );
    }

    public function getFilePath(): string
    {
        return $this->repo->getFilePath($this->rid);
    }

    public function deliver(string $filename): void
    {
        $this->repo->deliverFile($this->rid);
    }

    public function delete(): void
    {
        $this->repo->delete($this->obj_id, $this->rid);
    }

}
