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
 * Preview renderer class that is able to create previews from PDF, PS and EPS by using GhostScript.
 *
 * @author  Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
class ilGhostscriptRenderer extends ilFilePreviewRenderer
{
    private const SUPPORTED_FORMATS = "eps,pdf,pdfa,ps";

    /** @var string[]|null */
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
            self::$supported_formats = explode(",", self::SUPPORTED_FORMATS);
        }

        return self::$supported_formats;
    }

    /**
     * Determines whether Ghostscript is installed.
     */
    public static function isGhostscriptInstalled(): bool
    {
        return (defined('PATH_TO_GHOSTSCRIPT') && PATH_TO_GHOSTSCRIPT !== "");
    }

    /**
     * Renders the specified object into images.
     * The images do not need to be of the preview image size.
     *
     * @param ilObjFile $obj The object to create images from.
     * @return ilRenderedImage[] An array of ilRenderedImage containing the absolute file paths to the images.
     */
    protected function renderImages(\ilObject $obj): array
    {
        $numOfPreviews = $this->getMaximumNumberOfPreviews();

        // get file path
        $filepath = $obj->getFile();
        $inputFile = $this->prepareFileForExec($filepath);

        // create a temporary file name and remove its extension
        $output = str_replace(".tmp", "", ilFileUtils::ilTempnam());

        // use '#' instead of '%' as it gets replaced by 'escapeShellArg' on windows!
        $outputFile = $output . "_#02d.png";

        // create images with ghostscript (we use PNG here as it has better transparency quality)
        // gswin32c -dBATCH -dNOPAUSE -dSAFER -dFirstPage=1 -dLastPage=5 -sDEVICE=pngalpha -dEPSCrop -r72 -o $outputFile $inputFile
        // gswin32c -dBATCH -dNOPAUSE -dSAFER -dFirstPage=1 -dLastPage=5 -sDEVICE=jpeg -dJPEGQ=90 -r72 -o $outputFile $inputFile
        $args = sprintf(
            "-dBATCH -dNOPAUSE -dSAFER -dFirstPage=1 -dLastPage=%d -sDEVICE=pngalpha -dEPSCrop -r72 -o %s %s",
            $numOfPreviews,
            str_replace("#", "%", ilShellUtil::escapeShellArg($outputFile)),
            ilShellUtil::escapeShellArg($inputFile)
        );

        ilShellUtil::execQuoted(PATH_TO_GHOSTSCRIPT, $args);

        // was a temporary file created? then delete it
        if ($filepath !== $inputFile) {
            @unlink($inputFile);
        }

        // check each file and add it
        $images = array();
        $outputFile = str_replace("#", "%", $outputFile);

        for ($i = 1; $i <= $numOfPreviews; $i++) {
            $imagePath = sprintf($outputFile, $i);
            if (!file_exists($imagePath)) {
                break;
            }

            $images[] = new ilRenderedImage($imagePath);
        }

        return $images;
    }
}
