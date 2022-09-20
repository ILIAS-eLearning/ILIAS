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

/**
 * Storage of images in settings.
 */
class ilLearningSequenceFilesystem extends ilFileSystemAbstractionStorage
{
    public const IMG_ABSTRACT = 'abstract';
    public const IMG_EXTRO = 'extro';
    public const PATH_PRE = 'LSO';
    public const PATH_POST = 'Images';

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
    ): ilLearningSequenceSettings {
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


    public function delete_image(string $which, ilLearningSequenceSettings $settings): ilLearningSequenceSettings
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

    public function getStoragePathFor(string $which, int $obj_id, string $suffix): string
    {
        return $this->getStoragePath()
            . $which
            . '_'
            . $obj_id
            . '.'
            . $suffix;
    }

    public function getSuffix(string $file_name): string
    {
        return pathinfo($file_name, PATHINFO_EXTENSION);
    }

    protected function getStoragePath(): string
    {
        return  $this->getAbsolutePath() . '/';
    }

    /**
     * @inheritdoc
     */
    protected function getPathPrefix(): string
    {
        return self::PATH_PRE;
    }

    /**
     * @inheritdoc
     */
    protected function getPathPostfix(): string
    {
        return self::PATH_POST;
    }
}
