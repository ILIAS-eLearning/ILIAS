<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Preview/classes/class.ilFilePreviewRenderer.php");

/**
 * Represents an image that was created from a preview renderer and that can be 
 * further processed to create the preview.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
class ilRenderedImage
{
	/**
	 * The absolute path to the image.
	 * @var string
	 */
	private $img_path = null;
	
	/**
	 * Defines whether the image is temporary and can be deleted after
	 * the preview was created from the image.
	 * @var bool
	 */
	private $is_temporary = true;	
	
	/**
	 * Constructor
	 * 
	 * @param string $img_path The absolute path to the image.
	 * @param bool $is_temporary Defines whether the image is temporary and can be deleted after the preview was created.
	 */
	public function __construct($img_path, $is_temporary = true)
	{
		$this->img_path = $img_path;
		$this->is_temporary = $is_temporary;
	}
	
	/**
	 * Gets the absolute path to the rendered image.
	 * 
	 * @return string The absolute path to the rendered image.
	 */
	public function getImagePath()
	{
		return $this->img_path;
	}
	
	/**
	 * Defines whether the image is temporary and can be deleted after the preview was created.
	 * 
	 * @return bool true, if the image is temporary and can be deleted after the preview was created; otherwise, false.
	 */
	public function isTemporary()
	{
		return $this->is_temporary;
	}
	
	/**
	 * Deletes the image file if it is temporary.
	 */
	public function delete()
	{
		// only delete if not temporary
		if ($this->isTemporary() && is_file($this->getImagePath()))
		{
			@unlink($this->getImagePath());
		}
	}
}
?>