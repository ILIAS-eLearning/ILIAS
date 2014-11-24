<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Preview/classes/class.ilFilePreviewRenderer.php");

/**
 * Preview renderer class that is able to create previews from PDF, PS and EPS by using GhostScript.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
class ilGhostscriptRenderer extends ilFilePreviewRenderer
{
	// constants
	const SUPPORTED_FORMATS = "eps,pdf,pdfa,ps";
	
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
		if (self::$supported_formats == null)
			self::$supported_formats = explode(",", self::SUPPORTED_FORMATS);
		
		return self::$supported_formats;
	}
	
	/**
	 * Determines whether Ghostscript is installed.
	 */
	public static function isGhostscriptInstalled()
	{
		return (PATH_TO_GHOSTSCRIPT != "");
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
		$numOfPreviews = $this->getMaximumNumberOfPreviews();
		
		// get file path
		$filepath = $obj->getFile();
		$inputFile = $this->prepareFileForExec($filepath);
		
		// create a temporary file name and remove its extension
		$output = str_replace(".tmp", "", ilUtil::ilTempnam());
		
		// use '#' instead of '%' as it gets replaced by 'escapeShellArg' on windows!
		$outputFile = $output . "_#02d.png";		

		// create images with ghostscript (we use PNG here as it has better transparency quality)
		// gswin32c -dBATCH -dNOPAUSE -dSAFER -dFirstPage=1 -dLastPage=5 -sDEVICE=pngalpha -dEPSCrop -r72 -o $outputFile $inputFile
		// gswin32c -dBATCH -dNOPAUSE -dSAFER -dFirstPage=1 -dLastPage=5 -sDEVICE=jpeg -dJPEGQ=90 -r72 -o $outputFile $inputFile
		$args = sprintf(
			"-dBATCH -dNOPAUSE -dSAFER -dFirstPage=1 -dLastPage=%d -sDEVICE=pngalpha -dEPSCrop -r72 -o %s %s",
			$numOfPreviews,
			str_replace("#", "%", ilUtil::escapeShellArg($outputFile)),
			ilUtil::escapeShellArg($inputFile));
		
		ilUtil::execQuoted(PATH_TO_GHOSTSCRIPT, $args);
		
		// was a temporary file created? then delete it
		if ($filepath != $inputFile)
			@unlink($inputFile);
		
		// check each file and add it
		$images = array();
		$outputFile = str_replace("#", "%", $outputFile);
		
		for ($i = 1; $i <= $numOfPreviews; $i++)
		{
			$imagePath = sprintf($outputFile, $i);	
			if (!file_exists($imagePath))
				break;
			
			$images[] = new ilRenderedImage($imagePath);
		}
		
		return $images;
	}
}
?>