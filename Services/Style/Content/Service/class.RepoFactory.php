<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

use \ILIAS\Filesystem;
use \ILIAS\FileUpload\FileUpload;

/**
 * Content style internal repo service
 * @author Alexander Killing <killing@leifos.de>
 */
class RepoFactory
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var DataFactory
     */
    protected $data_factory;

    /**
     * @var ColorDBRepo
     */
    protected $color_repo;

    /**
     * @var CharacteristicDBRepo
     */
    protected $characteristic_repo;

    /**
     * @var CharacteristicCopyPasteSessionRepo
     */
    protected $characteristic_copy_paste_repo;

    /**
     * @var ImageFileRepo
     */
    protected $image_repo;

    /**
     * @var FileUpload
     */
    protected $upload;

    public function __construct(
        \ilDBInterface $db,
        DataFactory $data_factory,
        Filesystem\Filesystem $web_files,
        FileUpload $upload
    ) {
        $this->db = $db;
        $this->data_factory = $data_factory;
        $this->upload = $upload;

        $this->color_repo = new ColorDBRepo(
            $db,
            $data_factory
        );
        $this->characteristic_repo = new CharacteristicDBRepo(
            $db,
            $data_factory
        );
        $this->image_repo = new ImageFileRepo(
            $data_factory,
            $web_files,
            $upload
        );
        $this->characteristic_copy_paste_repo =
            new CharacteristicCopyPasteSessionRepo();
    }

    /**
     * @return CharacteristicDBRepo
     */
    public function characteristic(
    ) : CharacteristicDBRepo {
        return $this->characteristic_repo;
    }

    /**
     * @return CharacteristicCopyPasteSessionRepo
     */
    public function characteristicCopyPaste(
    ) : CharacteristicCopyPasteSessionRepo {
        return $this->characteristic_copy_paste_repo;
    }

    /**
     * @return ColorDBRepo
     */
    public function color() : ColorDBRepo
    {
        return $this->color_repo;
    }

    /**
     * @return ImageFileRepo
     */
    public function image() : ImageFileRepo
    {
        return $this->image_repo;
    }
}
