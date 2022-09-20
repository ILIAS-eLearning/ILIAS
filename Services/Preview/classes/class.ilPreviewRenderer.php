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

/**
 * Abstract parent class for all preview renderer classes.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
abstract class ilPreviewRenderer
{
    /**
     * Gets the name of the renderer.
     *
     * @return string The name of the renderer.
     */
    public function getName(): string
    {
        $name = get_class($this);

        if (strpos($name, "il") === 0) {
            $name = substr($name, 2);
        }

        if (strpos($name, "Renderer") === (strlen($name) - 8)) {
            $name = substr($name, 0, -8) . " Renderer";
        }

        return $name;
    }

    /**
     * Determines whether the renderer is a plugin or a built in one.
     *
     * @return bool true, if the renderer is a plugin; otherwise, false.
     */
    final public function isPlugin(): bool
    {
        return !is_file("./Services/Preview/classes/class." . get_class($this) . ".php");
    }

    /**
     * Gets an array containing the repository types (e.g. 'file' or 'crs') that are supported by the renderer.
     *
     * @return array An array containing the supported repository types.
     */
    abstract public function getSupportedRepositoryTypes(): array;

    /**
     * Determines whether the specified preview object is supported by the renderer.
     *
     * @param ilPreview $preview The preview object to check.
     * @return bool true, if the renderer supports the specified preview object; otherwise, false.
     */
    public function supports(\ilPreview $preview): bool
    {
        // contains type?
        return in_array($preview->getObjType(), $this->getSupportedRepositoryTypes());
    }

    /**
     * Creates the preview of the specified preview object.
     *
     * @param ilPreview $preview The preview object.
     * @param ilObject $obj The object to create a preview for.
     * @param bool $async true, if the rendering should be done asynchronously; otherwise, false.
     * @return bool true, if the preview was successfully rendered; otherwise, false.
     */
    final public function render(\ilPreview $preview, \ilObject $obj, bool $async): ?bool
    {
        $preview->setRenderDate(ilUtil::now());
        $preview->setRenderStatus(ilPreview::RENDER_STATUS_PENDING);
        $preview->save();
        $images = $this->renderImages($obj);

        // process each image
        if (is_array($images) && count($images) > 0) {
            $success = false;
            foreach ($images as $idx => $image) {
                // create the ending preview image
                $success |= $this->createPreviewImage(
                    $image->getImagePath(),
                    sprintf($preview->getFilePathFormat(), $idx + 1)
                );

                // if the image is temporary we can delete it
                if ($image->isTemporary()) {
                    $image->delete();
                }
            }

            $preview->setRenderDate(ilUtil::now());
            $preview->setRenderStatus($success ? ilPreview::RENDER_STATUS_CREATED : ilPreview::RENDER_STATUS_FAILED);
            return $success;
        }

        $preview->setRenderDate(ilUtil::now());
        $preview->setRenderStatus(ilPreview::RENDER_STATUS_FAILED);
        return false;
    }

    /**
     * Creates a preview image path from the specified source image.
     *
     * @param string $src_img_path The source image path.
     * @param string $dest_img_path The destination image path.
     * @return bool true, if the preview was created; otherwise, false.
     */
    private function createPreviewImage(string $src_img_path, string $dest_img_path): bool
    {
        // create resize argument
        $imgSize = $this->getImageSize();
        $resizeArg = $imgSize . "x" . $imgSize . (ilUtil::isWindows() ? "^" : "\\") . ">";

        // cmd: convert $src_img_path -background white -flatten -resize 280x280 -quality 85 -sharpen 0x0.5 $dest_img_path
        $args = sprintf(
            "%s -background white -flatten -resize %s -quality %d -sharpen 0x0.5 %s",
            ilShellUtil::escapeShellArg($src_img_path),
            $resizeArg,
            $this->getImageQuality(),
            ilShellUtil::escapeShellArg($dest_img_path)
        );

        ilShellUtil::execQuoted(PATH_TO_CONVERT, $args);

        return is_file($dest_img_path);
    }

    /**
     * Renders the specified object into images.
     * The images do not need to be of the preview image size.
     *
     * @param ilObject $obj The object to create images from.
     * @return array An array of ilRenderedImage containing the absolute file paths to the images.
     */
    abstract protected function renderImages(\ilObject $obj): array;

    /**
     * Gets the size of the preview images in pixels.
     *
     * @return int The current value
     */
    final protected function getImageSize(): int
    {
        return ilPreviewSettings::getImageSize();
    }

    /**
     * Gets the quality (compression) of the preview images (1-100).
     *
     * @return int The current value
     */
    final protected function getImageQuality(): int
    {
        return ilPreviewSettings::getImageQuality();
    }

    /**
     * Gets the maximum number of preview pictures per object.
     *
     * @return int The current value
     */
    final protected function getMaximumNumberOfPreviews(): int
    {
        return ilPreviewSettings::getMaximumPreviews();
    }
}
