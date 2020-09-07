<?php

declare(strict_types=1);

/**
 * Storage of images in settings.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLearningSequenceFilesystem extends ilFileSystemStorage
{
    const IMG_ABSTRACT = 'abstract';
    const IMG_EXTRO = 'extro';

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


    public function delete_image(string $which, ilLearningSequenceSettings $settings)
    {
        if ($which === self::IMG_ABSTRACT) {
            $delete = $settings->getAbstractImage();
            $settings = $settings->withAbstractImage(null);
        }
        if ($which === self::IMG_EXTRO) {
            $delete = $settings->getExtroImage();
            $settings = $settings->withExtroImage(null);
        }

        $this->deleteFile($delete);
        return $settings;
    }

    public function getStoragePathFor(string $which, int $obj_id, string $suffix)
    {
        return $this->getStoragePath()
            . $which
            . '_'
            . (string) $obj_id
            . '.'
            . $suffix;
    }

    public function getSuffix(string $file_name) : string
    {
        return pathinfo($file_name, PATHINFO_EXTENSION);
    }

    /**
     * @inheritdoc
     */
    protected function getStoragePath()
    {
        return  $this->getAbsolutePath() . '/';
    }

    /**
     * @inheritdoc
     */
    protected function getPathPrefix()
    {
        return 'LSO';
    }

    /**
     * @inheritdoc
     */
    protected function getPathPostfix()
    {
        return 'Images';
    }
}
