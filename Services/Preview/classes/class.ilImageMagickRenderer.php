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
 * Preview renderer class that is able to create previews from images by using ImageMagick.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
class ilImageMagickRenderer extends ilFilePreviewRenderer
{
    // constants
    private const SUPPORTED_FORMATS = "jpg,jpeg,jp2,png,gif,bmp,tif,tiff,cur,ico,pict,tga,psd";

    // variables
    private static ?array $supported_formats = null;

    /**
     * Gets an array containing the file formats that are supported by the renderer.
     *
     * @return array An array containing the supported file formats.
     */
    public function getSupportedFileFormats(): array
    {
        // build formats only once
        if (!isset(self::$supported_formats)) {
            self::$supported_formats = self::evaluateSupportedFileFormats();
        }

        return self::$supported_formats;
    }

    /**
     * Evaluates the supported file formats.
     *
     * @return array An array containing the supported file formats.
     */
    public static function evaluateSupportedFileFormats(): array
    {
        return explode(",", self::SUPPORTED_FORMATS) ?? [];
    }

    /**
     * Renders the specified object into images.
     * The images do not need to be of the preview image size.
     *
     * @param ilObjFile $obj The object to create images from.
     * @return array An array of ilRenderedImage containing the absolute file paths to the images.
     */
    protected function renderImages(\ilObject $obj): array
    {
        $filepath = $obj->getFile();
        $tmpPath = $this->prepareFileForExec($filepath);
        $isTemporary = $tmpPath !== $filepath;
        return array(new ilRenderedImage($tmpPath, $isTemporary));
    }
}
