<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Preview/classes/class.ilFilePreviewRenderer.php");

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
    const SUPPORTED_FORMATS = "jpg,jpeg,jp2,png,gif,bmp,tif,tiff,cur,ico,pict,tga,psd";
    
    // variables
    private static $supported_formats = null;
    
    /**
     * Gets an array containing the file formats that are supported by the renderer.
     *
     * @return array An array containing the supported file formats.
     */
    public function getSupportedFileFormats()
    {
        // build formats only once
        if (self::$supported_formats == null) {
            self::$supported_formats = self::evaluateSupportedFileFormats();
        }
        
        return self::$supported_formats;
    }
    
    /**
     * Evaluates the supported file formats.
     *
     * @return array An array containing the supported file formats.
     */
    public static function evaluateSupportedFileFormats()
    {
        $formats = explode(",", self::SUPPORTED_FORMATS);
        return $formats;
    }
    
    /**
     * Renders the specified object into images.
     * The images do not need to be of the preview image size.
     *
     * @param ilObjFile $obj The object to create images from.
     * @return array An array of ilRenderedImage containing the absolute file paths to the images.
     */
    protected function renderImages($obj)
    {
        $filepath = $obj->getFile();
        $tmpPath = $this->prepareFileForExec($filepath);
        $isTemporary = $tmpPath != $filepath;
        return array(new ilRenderedImage($tmpPath . "[0]", $isTemporary));
    }
}
