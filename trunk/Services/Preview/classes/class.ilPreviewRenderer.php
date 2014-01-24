<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Preview/classes/class.ilPreviewSettings.php");

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
	public function getName()
	{
		$name = get_class($this);
		
		if (strpos($name, "il") === 0)
			$name = substr($name, 2);
		
		if (strpos($name, "Renderer") === (strlen($name) - 8))
			$name = substr($name, 0, strlen($name) - 8) . " Renderer";
		
		return $name;
	}
	
	/**
	 * Determines whether the renderer is a plugin or a built in one.
	 * 
	 * @return bool true, if the renderer is a plugin; otherwise, false.
	 */
	public final function isPlugin()
	{
		$filepath = "./Services/Preview/classes/class." . get_class($this) . ".php";
		return !is_file($filepath);
	}
	
	/**
	 * Gets an array containing the repository types (e.g. 'file' or 'crs') that are supported by the renderer.
	 * 
	 * @return array An array containing the supported repository types.
	 */
	public abstract function getSupportedRepositoryTypes();
	
	/**
	 * Determines whether the specified preview object is supported by the renderer.
	 * 
	 * @param ilPreview $preview The preview object to check.
	 * @return bool true, if the renderer supports the specified preview object; otherwise, false.
	 */
	public function supports($preview)
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
	public final function render($preview, $obj, $async)
	{
		$preview->setRenderDate(ilUtil::now());
		$preview->setRenderStatus(ilPreview::RENDER_STATUS_PENDING);
		$preview->save();
		
		// TODO: this should be done in background if $async is true
		
		// the deriving renderer should deliver images
		require_once("./Services/Preview/classes/class.ilRenderedImage.php");
		$images = $this->renderImages($obj);
		
		// process each image
		if (is_array($images) && count($images) > 0)
		{
			$success = false;
			foreach ($images as $idx => $image)	
			{
				// create the ending preview image
				$success |= $this->createPreviewImage(
					$image->getImagePath(), 
					sprintf($preview->getFilePathFormat(), $idx + 1));

				// if the image is temporary we can delete it
				if($image->isTemporary())
					$image->delete();					
			}

			$preview->setRenderDate(ilUtil::now());
			$preview->setRenderStatus($success ? ilPreview::RENDER_STATUS_CREATED : ilPreview::RENDER_STATUS_FAILED);
			return $success;
		}
		else
		{
			$preview->setRenderDate(ilUtil::now());
			$preview->setRenderStatus(ilPreview::RENDER_STATUS_FAILED);
			return false;
		}
	}
	
	/**
	 * Creates a preview image path from the specified source image.
	 * 
	 * @param string $src_img_path The source image path.
	 * @param string $dest_img_path The destination image path.
	 * @return bool true, if the preview was created; otherwise, false.
	 */
	private function createPreviewImage($src_img_path, $dest_img_path)
	{
		// create resize argument
		$imgSize = $this->getImageSize();
		$resizeArg = $imgSize . "x" . $imgSize . (ilUtil::isWindows() ? "^" : "\\") . ">";
		
		// cmd: convert $src_img_path -background white -flatten -resize 280x280 -quality 85 -sharpen 0x0.5 $dest_img_path
		$args = sprintf(
			"%s -background white -flatten -resize %s -quality %d -sharpen 0x0.5 %s",
			ilUtil::escapeShellArg($src_img_path),
			$resizeArg,
			$this->getImageQuality(),
			ilUtil::escapeShellArg($dest_img_path));
		
		ilUtil::execConvert($args);
		
		return is_file($dest_img_path);
	}
	
	/**
	 * Renders the specified object into images.
	 * The images do not need to be of the preview image size.
	 * 
	 * @param ilObject $obj The object to create images from.
	 * @return array An array of ilRenderedImage containing the absolute file paths to the images.
	 */
	protected abstract function renderImages($obj);
	
	/**
	 * Gets the size of the preview images in pixels.
	 * 
	 * @return int The current value
	 */
	protected final function getImageSize()
	{
		return ilPreviewSettings::getImageSize();
	}
	
	/**
	 * Gets the quality (compression) of the preview images (1-100).
	 * 
	 * @return int The current value
	 */
	protected final function getImageQuality()
	{
		return ilPreviewSettings::getImageQuality();
	}
	
	/**
	 * Gets the maximum number of preview pictures per object.
	 * 
	 * @return int The current value
	 */
	protected final function getMaximumNumberOfPreviews()
	{
		return ilPreviewSettings::getMaximumPreviews();
	}
}
?>
