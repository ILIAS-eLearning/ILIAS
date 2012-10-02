<?php

/* Copyright (c) 1998-2012 ILIAS open source, GPL, see docs/LICENSE */

/**
* User interface for media player. Wraps flash mp3 player and similar tools.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesMediaObjects
*/
class ilMediaPlayerGUI
{
	var $file;
	var $displayHeight = "480";
	var $displayWidth = "640";
	var $mimeType;
	static $nr = 1;
	static $lightbox_initialized = false;
	var $current_nr;

	function __construct($a_id = "")
	{
		$this->id = $a_id;
		$this->current_nr = self::$nr;
		self::$nr++;
	}

	/**
	* Set File.
	*
	* @param	string	$a_file	File
	*/
	function setFile($a_file)
	{
		$this->file = $a_file;
	}

	/**
	* Get File.
	*
	* @return	string	File
	*/
	function getFile()
	{
		return $this->file;
	}
	
	/**
	 * Set alternative video file
	 *
	 * @param string $a_val alternative video file	
	 */
	function setAlternativeVideoFile($a_val)
	{
		$this->alt_video_file = $a_val;
	}
	
	/**
	 * Get alternative video file
	 *
	 * @return string alternative video file
	 */
	function getAlternativeVideoFile()
	{
		return $this->alt_video_file;
	}
	
	/**
	 * Set alternative video mime type
	 *
	 * @param string $a_val alternative video mime type	
	 */
	function setAlternativeVideoMimeType($a_val)
	{
		$this->alt_video_mime = $a_val;
	}
	
	/**
	 * Get alternative video mime type
	 *
	 * @return string alternative video mime type
	 */
	function getAlternativeVideoMimeType()
	{
		return $this->alt_video_mime;
	}

	/**
	 * set display height
	 *
	 * @param int $dHeight
	 */
	function setDisplayHeight ($dHeight) {
		$this->displayHeight = $dHeight;
	}
	
	/**
	 * return display height of player.
	 *
	 * @return int
	 */
	function getDisplayHeight () {
		return $this->displayHeight;
	}

	/**
	 * Set display width
	 *
	 * @param string $a_val display width	
	 */
	function setDisplayWidth($a_val)
	{
		$this->displayWidth = $a_val;
	}
	
	/**
	 * Get display width
	 *
	 * @return string display width
	 */
	function getDisplayWidth()
	{
		return $this->displayWidth;
	}

	function setMimeType ($value) {
	    $this->mimeType = $value;
	}

	/**
	 * Set video preview picture
	 *
	 * @param string $a_val video preview picture	
	 */
	function setVideoPreviewPic($a_val, $a_alt = "")
	{
		$this->video_preview_pic = $a_val;
		$this->video_preview_pic_alt = $a_alt;
	}
	
	/**
	 * Get video preview picture
	 *
	 * @return string video preview picture
	 */
	function getVideoPreviewPic()
	{
		return $this->video_preview_pic;
	}
	
	/**
	* Get Html for MP3 Player
	*/
	function getMp3PlayerHtml($a_preview = false)
	{
		global $tpl;
		
		$tpl->addJavascript("./Services/MediaObjects/js/MediaObjects.js");
		
		if (!self::$lightbox_initialized)
		{
			include_once("./Services/UIComponent/Lightbox/classes/class.ilLightboxGUI.php");
			$lb = new ilLightboxGUI("media_lightbox");
			$lb->setWidth("660px");
			$lb->addLightbox();
			self::$lightbox_initialized = true;
		}
		
		require_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
		include_once("./Services/MediaObjects/classes/class.ilExternalMediaAnalyzer.php");

		// youtube
/*		if (ilExternalMediaAnalyzer::isYouTube($this->getFile()))
		{
			$p = ilExternalMediaAnalyzer::extractYouTubeParameters($this->getFile());
			$html = '<object width="320" height="240">'.
				'<param name="movie" value="http://www.youtube.com/v/'.$p["v"].'?fs=1">'.
				'</param><param name="allowFullScreen" value="true"></param>'.
				'<param name="allowscriptaccess" value="always">'.
				'</param><embed src="http://www.youtube.com/v/'.$p["v"].'?fs=1" '.
				'type="application/x-shockwave-flash" allowscriptaccess="always" '.
				'allowfullscreen="true" width="320" height="240"></embed></object>';
			return $html;
		}

		// vimeo
		if (ilExternalMediaAnalyzer::isVimeo($this->getFile()))
		{
			$p = ilExternalMediaAnalyzer::extractVimeoParameters($this->getFile());

			$html = '<iframe src="http://player.vimeo.com/video/'.$p["id"].'" width="320" height="240" '.
				'frameborder="0"></iframe>';

			return $html;
		}
*/
		$mimeType = $this->mimeType == "" ? ilObjMediaObject::getMimeType(basename($this->getFile())) : $this->mimeType;
		include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");
		
		// video tag
		if (in_array($mimeType, array("video/mp4", "video/m4v", "video/rtmp",
			"video/x-flv", "video/webm")))
		{
			ilPlayerUtil::initMediaElementJs();

			if ($mimeType == "video/quicktime")
			{
				$mimeType = "video/mov";
			}
			
			$mp_tpl = new ilTemplate("tpl.flv_player.html", true, true, "Services/MediaObjects");
			
			// preview
			if ($a_preview)
			{
				$mp_tpl->setCurrentBlock("preview");
				if ($this->getVideoPreviewPic() != "")
				{
					$mp_tpl->setVariable("IMG_SRC", $this->getVideoPreviewPic());
				}
				else
				{
					$mp_tpl->setVariable("IMG_SRC", ilUtil::getImagePath("mcst_preview.png"));
				}
				$mp_tpl->setVariable("IMG_ALT", $this->video_preview_pic_alt);
				$mp_tpl->parseCurrentBlock();
			}
			
			// sources
			$mp_tpl->setCurrentBlock("source");
			$mp_tpl->setVariable("FILE", $this->getFile());
			$mp_tpl->setVariable("MIME", $mimeType);
			$mp_tpl->parseCurrentBlock();

			if (in_array($this->getAlternativeVideoMimeType(), array("video/mp4", "video/webm")))
			{
				$mp_tpl->setCurrentBlock("source");
				$mp_tpl->setVariable("FILE", $this->getAlternativeVideoFile());
				$mp_tpl->setVariable("MIME", $this->getAlternativeVideoMimeType());
				$mp_tpl->parseCurrentBlock();
			}
			
			$mp_tpl->setCurrentBlock("mejs_video");
			
			if ($a_preview)
			{
				$mp_tpl->setVariable("CLASS", "ilNoDisplay");
			}
			
			$mp_tpl->setVariable("PLAYER_NR", $this->current_nr);
			$height = $this->getDisplayHeight();
			$width = $this->getDisplayWidth();
			if (is_int(strpos($mimeType,"audio/mpeg")))
			{
				$height = "30";
			}

			$mp_tpl->setVariable("DISPLAY_HEIGHT", $height);
			$mp_tpl->setVariable("DISPLAY_WIDTH", $width);
			$mp_tpl->setVariable("PREVIEW_PIC", $this->getVideoPreviewPic());
			$mp_tpl->setVariable("SWF_FILE", ilPlayerUtil::getFlashVideoPlayerFilename(true));
			$mp_tpl->setVariable("FFILE", $this->getFile());
			$mp_tpl->parseCurrentBlock();
			$r = $mp_tpl->get();

			return $r;
		}

		// audio/mpeg
		if (is_int(strpos($mimeType,"audio/mpeg")))
		{
			ilPlayerUtil::initMediaElementJs();
			$mp_tpl = new ilTemplate("tpl.flv_player.html", true, true, "Services/MediaObjects");
			$mp_tpl->setCurrentBlock("audio");
			$mp_tpl->setVariable("AFILE", $this->getFile());
			$mp_tpl->setVariable("APLAYER_NR", $this->current_nr);
			$mp_tpl->setVariable("AHEIGHT", "30");
			$mp_tpl->setVariable("AWIDTH", "320");
			$mp_tpl->parseCurrentBlock();
			return $mp_tpl->get();
		}
return;
		$tpl->addJavaScript("./Services/MediaObjects/flash_flv_player/swfobject.js");		
		$mp_tpl = new ilTemplate("tpl.flv_player.html", true, true, "Services/MediaObjects");
		$mp_tpl->setCurrentBlock("default");
		$mp_tpl->setVariable("FILE", urlencode($this->getFile()));
		$mp_tpl->setVariable("PLAYER_NR", $this->current_nr);
		$mp_tpl->setVariable("DISPLAY_HEIGHT", strpos($mimeType,"audio/mpeg") === false ? "240" : "20");
		$mp_tpl->setVariable("DISPLAY_WIDTH", "320");
		$mp_tpl->parseCurrentBlock();
		return $mp_tpl->get();
	}
	
	/**
	 * Get preview html
	 *
	 * @param
	 * @return
	 */
	function getPreviewHtml()
	{
		return $this->getMp3PlayerHtml(true);
	}
	
}
?>
