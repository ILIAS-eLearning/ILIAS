<?php declare(strict_types=1);

/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Storage of images in settings.
 */
class ilLearningSequenceFilesystem extends ilFileSystemAbstractionStorage
{
    const IMG_ABSTRACT = 'abstract';
    const IMG_EXTRO = 'extro';
    const PATH_PRE = 'LSO';
    const PATH_POST = 'Images';

    public function __construct()
    {
        parent::__construct(self::STORAGE_WEB, false, 0);
        if (!is_dir($this->getAbsolutePath())) {
            $this->create();
        }
    }

    public function moveUploaded(
        string $which,
        array $file_info,
        ilLearningSequenceSettings $settings
    ) : ilLearningSequenceSettings {
        $target = $this->getStoragePathFor(
            $which,
            $settings->getObjId(),
            $this->getSuffix($file_info['name'])
        );
        move_uploaded_file($file_info['tmp_name'], $target);

        if ($which === self::IMG_ABSTRACT) {
            $settings = $settings->withAbstractImage($target);
        }
        if ($which === self::IMG_EXTRO) {
            $settings = $settings->withExtroImage($target);
        }
        return $settings;
    }


    public function delete_image(string $which, ilLearningSequenceSettings $settings) : ilLearningSequenceSettings
    {
        $delete = '';
        if ($which === self::IMG_ABSTRACT) {
            $delete = $settings->getAbstractImage();
            $settings = $settings->withAbstractImage();
        }
        if ($which === self::IMG_EXTRO) {
            $delete = $settings->getExtroImage();
            $settings = $settings->withExtroImage();
        }

        $this->deleteFile($delete);
        return $settings;
    }

    public function getStoragePathFor(string $which, int $obj_id, string $suffix) : string
    {
        return $this->getStoragePath()
            . $which
            . '_'
            . $obj_id
            . '.'
            . $suffix;
    }

    public function getSuffix(string $file_name) : string
    {
        return pathinfo($file_name, PATHINFO_EXTENSION);
    }

    protected function getStoragePath() : string
    {
        return  $this->getAbsolutePath() . '/';
    }

    /**
     * @inheritdoc
     */
    protected function getPathPrefix():string
    {
        return self::PATH_PRE;
    }

    /**
     * @inheritdoc
     */
    protected function getPathPostfix():string
    {
        return self::PATH_POST;
    }
}
