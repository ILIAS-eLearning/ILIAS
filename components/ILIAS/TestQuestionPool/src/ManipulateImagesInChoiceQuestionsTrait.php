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

namespace ILIAS\TestQuestionPool;

trait ManipulateImagesInChoiceQuestionsTrait
{
    /**
     * Deletes an image file
     *
     * @param string $image_filename Name of the image file to delete
     */
    protected function deleteImage($image_filename): void
    {
        $imagepath = $this->getImagePath();
        @unlink($imagepath . $image_filename);
        $thumbpath = $imagepath . $this->getThumbPrefix() . $image_filename;
        @unlink($thumbpath);
    }

    private function cloneImages(
        int $source_question_id,
        int $source_parent_id,
        int $target_question_id,
        int $target_parent_id,
        array $answers
    ): void {
        $image_source_path = $this->getImagePath($source_question_id, $source_parent_id);
        $image_target_path = $this->getImagePath($target_question_id, $target_parent_id);

        if (!file_exists($image_target_path)) {
            \ilFileUtils::makeDirParents($image_target_path);
        } else {
            $this->removeAllImageFiles($image_target_path);
        }

        foreach ($answers as $answer) {
            $filename = $answer->getImage();
            if ($filename === null || $filename === '') {
                continue;
            }

            if (!file_exists($image_source_path . $filename)
                || !copy($image_source_path . $filename, $image_target_path . $filename)) {
                $this->log->root()->warning('Image could not be cloned for object for question: ' . $target_question_id);
            }
            if (!file_exists($image_source_path . $this->getThumbPrefix() . $filename)
                || !copy($image_source_path . $this->getThumbPrefix() . $filename, $image_target_path . $this->getThumbPrefix() . $filename)) {
                $this->log->root()->warning('Image thumbnail could not be cloned for object for question: ' . $target_question_id);
            }
        }
    }

    public function rebuildThumbnails(
        bool $is_single_line,
        int $thumbnail_size,
        string $image_path,
        array $answers
    ): array {
        if (!$is_single_line || $thumbnail_size === 0) {
            return $answers;
        }

        foreach ($answers as $answer) {
            if ($answer->getImage() === null || $answer->getImage() === '') {
                continue;
            }

            $thumb_path = $image_path . $this->getThumbPrefix() . $answer->getImage();
            if (file_exists($thumb_path)) {
                unlink($thumb_path);
            }

            $current_file_path = $image_path . $answer->getImage();
            if (!file_exists($current_file_path)) {
                continue;
            }
            $new_file_name = $this->buildHashedImageFilename($answer->getImage(), true);
            $new_file_path = $image_path . $new_file_name;
            rename($current_file_path, $new_file_path);
            $answer->setImage($new_file_name);

            $this->generateThumbForFile(
                $new_file_name,
                $image_path,
                $thumbnail_size
            );
        }

        return $answers;
    }

    public function getThumbPrefix(): string
    {
        return "thumb.";
    }

    public function generateThumbForFile(
        string $file_name,
        string $image_path,
        int $thumbnail_size,
    ): void {
        $file_path = $image_path . $file_name;
        if (!file_exists($file_path)) {
            return;
        }

        $thumb_path = $image_path . $this->getThumbPrefix() . $file_name;
        $path_info = pathinfo($file_path);
        $ext = "";
        switch (strtoupper($path_info['extension'])) {
            case 'PNG':
                $ext = 'PNG';
                break;
            case 'GIF':
                $ext = 'GIF';
                break;
            default:
                $ext = 'JPEG';
                break;
        }
        \ilShellUtil::convertImage($file_path, $thumb_path, $ext, (string) $thumbnail_size, );
    }
}
