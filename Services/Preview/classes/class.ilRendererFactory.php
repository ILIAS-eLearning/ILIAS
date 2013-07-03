<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Preview/classes/class.ilPreview.php");

/**
 * Factory that provides access to all available preview renderers. 
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
final class ilRendererFactory
{
	/**
	 * The available renderers.
	 * @var array
	 */
	private static $renderers = null;
	
	/**
	 * Gets an array containing all available preview renderers.
	 * 
	 * @return array All available preview renderers.
	 */
	public static function getRenderers()
	{
		self::loadAvailableRenderers();
		return self::$renderers;
	}
	
	/**
	 * Gets the renderer that is able to create a preview for the specified preview object.
	 * 
	 * @param ilPReview $preview The preview to get the renderer for.
	 * @return ilPreviewRenderer A renderer or null if no renderer matches the preview object.
	 */
	public static function getRenderer($preview)
	{
		$renderers = self::getRenderers();
		
		// check each renderer if it supports that preview object
		foreach ($renderers as $renderer)
		{
			if ($renderer->supports($preview))	
			{
				return $renderer;
			}			
		}
		
		// no matching renderer was found
		return null;
	}
	
	/**
	 * Loads the available preview renderers. That is built in renderers and plugins.
	 * 
	 * @return array The available renderers.
	 */
	private static function loadAvailableRenderers()
	{
		// already loaded?
		if (self::$renderers != null)
			return;
		
		$r = array();
		
		// get registered and active plugins
		global $ilPluginAdmin;
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "Preview", "pvre");
		foreach ($pl_names as $pl)
		{
			$plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "Preview", "pvre", $pl);
			$r[] = $plugin->getRendererClassInstance();	
		}
		
		// add default renderers
		include_once("./Services/Preview/classes/class.ilImageMagickRenderer.php");
		$r[] = new ilImageMagickRenderer();
		
		include_once("./Services/Preview/classes/class.ilGhostscriptRenderer.php");
		if (ilGhostscriptRenderer::isGhostscriptInstalled())
			$r[] = new ilGhostscriptRenderer();
		
		self::$renderers = $r;
	}
}
?>
