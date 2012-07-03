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
	var $displayHeight;
	var $mimeType;
	static $nr = 1;

	function __construct()
	{
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


	function setMimeType ($value) {
	    $this->mimeType = $value;
	}

	/**
	 * Set video preview picture
	 *
	 * @param string $a_val video preview picture	
	 */
	function setVideoPreviewPic($a_val)
	{
		$this->video_preview_pic = $a_val;
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
	function getMp3PlayerHtml()
	{
		global $tpl;
		require_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
		include_once("./Services/MediaObjects/classes/class.ilExternalMediaAnalyzer.php");

		// youtube
		if (ilExternalMediaAnalyzer::isYouTube($this->getFile()))
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
		$mimeType = $this->mimeType == "" ? ilObjMediaObject::getMimeType(basename($this->getFile())) : $this->mimeType;
		
		include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");
		
		// video tag
		if ($mimeType == "video/mp4")
		{
			$tpl->addCss("./Services/MediaObjects/media_element_2_7_0/mediaelementplayer.min.css");
			$tpl->addJavaScript("./Services/MediaObjects/media_element_2_7_0/mediaelement-and-player.min.js");

			$mp_tpl = new ilTemplate("tpl.flv_player.html", true, true, "Services/MediaObjects");
			$mp_tpl->setCurrentBlock("video");
			//$mp_tpl->setVariable("FILE", urlencode($this->getFile()));
			$mp_tpl->setVariable("FILE", $this->getFile());
			$mp_tpl->setVariable("PLAYER_NR", self::$nr);
			$mp_tpl->setVariable("DISPLAY_HEIGHT", strpos($mimeType,"audio/mpeg") === false ? "240" : "30");
			$mp_tpl->setVariable("DISPLAY_WIDTH", "320");
			$mp_tpl->setVariable("PREVIEW_PIC", $this->getVideoPreviewPic());
			$mp_tpl->setVariable("SWF_FILE", ilPlayerUtil::getFlashVideoPlayerFilename(true));
			self::$nr++;
			$mp_tpl->parseCurrentBlock();
			return $mp_tpl->get();
		}
		
		// flv
		if (is_int(strpos($mimeType,"flv")))
		{
			$mp_tpl = new ilTemplate("tpl.flv_player.html", true, true, "Services/MediaObjects");
			$mp_tpl->setCurrentBlock("flv");
			$mp_tpl->setVariable("FILE", urlencode($this->getFile()));
			$mp_tpl->setVariable("PLAYER_NR", self::$nr);
			$mp_tpl->setVariable("DISPLAY_HEIGHT", strpos($mimeType,"audio/mpeg") === false ? "240" : "30");
			$mp_tpl->setVariable("DISPLAY_WIDTH", "320");
			$mp_tpl->setVariable("SWF_FILE", ilPlayerUtil::getFlashVideoPlayerFilename(true));
			self::$nr++;
			$mp_tpl->parseCurrentBlock();
			return $mp_tpl->get();
		}
		
		// audio/mpeg
		if (is_int(strpos($mimeType,"audio/mpeg")))
		{
			$tpl->addCss("./Services/MediaObjects/media_element_2_7_0/mediaelementplayer.min.css");
			$tpl->addJavaScript("./Services/MediaObjects/media_element_2_7_0/mediaelement-and-player.min.js");
			$mp_tpl = new ilTemplate("tpl.flv_player.html", true, true, "Services/MediaObjects");
			$mp_tpl->setCurrentBlock("audio");
			$mp_tpl->setVariable("AFILE", $this->getFile());
			$mp_tpl->setVariable("APLAYER_NR", self::$nr);
			$mp_tpl->setVariable("AHEIGHT", "30");
			$mp_tpl->setVariable("AWIDTH", "320");
			self::$nr++;
			$mp_tpl->parseCurrentBlock();
			return $mp_tpl->get();
		}

		$tpl->addJavaScript("./Services/MediaObjects/flash_flv_player/swfobject.js");		
		$mp_tpl = new ilTemplate("tpl.flv_player.html", true, true, "Services/MediaObjects");
		$mp_tpl->setCurrentBlock("default");
		$mp_tpl->setVariable("FILE", urlencode($this->getFile()));
		$mp_tpl->setVariable("PLAYER_NR", self::$nr);
		$mp_tpl->setVariable("DISPLAY_HEIGHT", strpos($mimeType,"audio/mpeg") === false ? "240" : "20");
		$mp_tpl->setVariable("DISPLAY_WIDTH", "320");
		self::$nr++;
		$mp_tpl->parseCurrentBlock();
		return $mp_tpl->get();
	}
}
?>
