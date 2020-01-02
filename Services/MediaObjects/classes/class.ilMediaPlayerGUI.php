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
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $file;
    protected $displayHeight = "480";
    protected $displayWidth = "640";
    protected $mimeType;
    protected static $nr = 1;
    protected static $lightbox_initialized = false;
    protected $current_nr;
    protected $title;
    protected $description;
    protected $event_callback_url = "";
    protected $download_link = "";

    public function __construct($a_id = "", $a_event_callback_url = "")
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->id = $a_id;
        $this->event_callback_url = $a_event_callback_url;
        $this->current_nr = self::$nr;
        self::$nr++;
    }

    /**
    * Set File.
    *
    * @param	string	$a_file	File
    */
    public function setFile($a_file)
    {
        $this->file = $a_file;
    }

    /**
    * Get File.
    *
    * @return	string	File
    */
    public function getFile()
    {
        return $this->file;
    }
    
    /**
     * Set alternative video file
     *
     * @param string $a_val alternative video file
     */
    public function setAlternativeVideoFile($a_val)
    {
        $this->alt_video_file = $a_val;
    }
    
    /**
     * Get alternative video file
     *
     * @return string alternative video file
     */
    public function getAlternativeVideoFile()
    {
        return $this->alt_video_file;
    }
    
    /**
     * Set alternative video mime type
     *
     * @param string $a_val alternative video mime type
     */
    public function setAlternativeVideoMimeType($a_val)
    {
        $this->alt_video_mime = $a_val;
    }
    
    /**
     * Get alternative video mime type
     *
     * @return string alternative video mime type
     */
    public function getAlternativeVideoMimeType()
    {
        return $this->alt_video_mime;
    }

    /**
     * set display height
     *
     * @param int $dHeight
     */
    public function setDisplayHeight($dHeight)
    {
        $this->displayHeight = $dHeight;
    }
    
    /**
     * return display height of player.
     *
     * @return int
     */
    public function getDisplayHeight()
    {
        return $this->displayHeight;
    }

    /**
     * Set display width
     *
     * @param string $a_val display width
     */
    public function setDisplayWidth($a_val)
    {
        $this->displayWidth = $a_val;
    }
    
    /**
     * Get display width
     *
     * @return string display width
     */
    public function getDisplayWidth()
    {
        return $this->displayWidth;
    }

    public function setMimeType($value)
    {
        $this->mimeType = $value;
    }

    /**
     * Set video preview picture
     *
     * @param string $a_val video preview picture
     */
    public function setVideoPreviewPic($a_val, $a_alt = "")
    {
        $this->video_preview_pic = $a_val;
        $this->video_preview_pic_alt = $a_alt;
    }
    
    /**
     * Get video preview picture
     *
     * @return string video preview picture
     */
    public function getVideoPreviewPic()
    {
        return $this->video_preview_pic;
    }
    
    /**
     * Set Title
     *
     * @param string $a_val title
     */
    public function setTitle($a_val)
    {
        $this->title = $a_val;
    }
    
    /**
     * Get Title
     *
     * @return string title
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set description
     *
     * @param string $a_val description
     */
    public function setDescription($a_val)
    {
        $this->description = $a_val;
    }
    
    /**
     * Get description
     *
     * @return string description
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Set force audio preview
     *
     * @param boolean $a_val force audio preview picture
     */
    public function setForceAudioPreview($a_val)
    {
        $this->force_audio_preview = $a_val;
    }
    
    /**
     * Get force audio preview
     *
     * @return boolean force audio preview picture
     */
    public function getForceAudioPreview()
    {
        return $this->force_audio_preview;
    }
    
    /**
     * Set download link
     *
     * @param string $a_val download link
     */
    public function setDownloadLink($a_val)
    {
        $this->download_link = $a_val;
    }
    
    /**
     * Get download link
     *
     * @return string download link
     */
    public function getDownloadLink()
    {
        return $this->download_link;
    }

    /**
     * Init Javascript
     * @param null $a_tpl
     */
    public static function initJavascript($a_tpl = null)
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        if ($a_tpl == null) {
            $a_tpl = $tpl;
        }

        include_once("./Services/YUI/classes/class.ilYuiUtil.php");
        ilYuiUtil::initConnection();

        $a_tpl->addJavascript("./Services/MediaObjects/js/MediaObjects.js");

        include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");
        ilPlayerUtil::initMediaElementJs($a_tpl);
    }


    /**
    * Get Html for MP3 Player
    */
    public function getMp3PlayerHtml($a_preview = false)
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        self::initJavascript($tpl);

        if (!self::$lightbox_initialized && $a_preview) {
            include_once("./Services/UIComponent/Lightbox/classes/class.ilLightboxGUI.php");
            $lb = new ilLightboxGUI("media_lightbox");
            $lb->setWidth("660px");
            $lb->addLightbox();
            self::$lightbox_initialized = true;
        }
        
        require_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
        include_once("./Services/MediaObjects/classes/class.ilExternalMediaAnalyzer.php");

        // youtube
        if (ilExternalMediaAnalyzer::isYouTube($this->getFile())) {
            $p = ilExternalMediaAnalyzer::extractYouTubeParameters($this->getFile());
            /*
            $html = '<object width="320" height="240">'.
                '<param name="movie" value="http://www.youtube.com/v/'.$p["v"].'?fs=1">'.
                '</param><param name="allowFullScreen" value="true"></param>'.
                '<param name="allowscriptaccess" value="always">'.
                '</param><embed src="http://www.youtube.com/v/'.$p["v"].'?fs=1" '.
                'type="application/x-shockwave-flash" allowscriptaccess="always" '.
                'allowfullscreen="true" width="320" height="240"></embed></object>';
            return $html;*/
            $mp_tpl = new ilTemplate("tpl.flv_player.html", true, true, "Services/MediaObjects");
            if ($a_preview) {
                if ($this->getDownloadLink() != "") {
                    $mp_tpl->setCurrentBlock("ytdownload");
                    $mp_tpl->setVariable("TXT_DOWNLOAD", $lng->txt("download"));
                    $mp_tpl->setVariable("HREF_DOWNLOAD", $this->getDownloadLink());
                    $mp_tpl->parseCurrentBlock();
                }

                $mp_tpl->setCurrentBlock("ytpreview");
                if ($this->getVideoPreviewPic() != "") {
                    $mp_tpl->setVariable("IMG_SRC", $this->getVideoPreviewPic());
                } else {
                    $mp_tpl->setVariable("IMG_SRC", ilUtil::getImagePath("mcst_preview.svg"));
                }
                $height = $this->getDisplayHeight();
                $width = $this->getDisplayWidth();
                $mp_tpl->setVariable("DISPLAY_HEIGHT", $height);
                $mp_tpl->setVariable("DISPLAY_WIDTH", $width);
                $mp_tpl->setVariable("IMG_ALT", $this->video_preview_pic_alt);
                $mp_tpl->setVariable("PTITLE", $this->getTitle());
                $mp_tpl->parseCurrentBlock();
            }
            $mp_tpl->setCurrentBlock("youtube");
            if ($a_preview) {
                $mp_tpl->setVariable("CLASS", "ilNoDisplay");
            }
            $mp_tpl->setVariable("PV", $p["v"]);
            $mp_tpl->setVariable("PLAYER_NR", $this->id . "_" . $this->current_nr);
            $mp_tpl->setVariable("TITLE", $this->getTitle());
            $mp_tpl->setVariable("DESCRIPTION", $this->getDescription());
            include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
            if ($a_preview) {
                $mp_tpl->setVariable("CLOSE", ilGlyphGUI::get(ilGlyphGUI::CLOSE));
            }
            $mp_tpl->parseCurrentBlock();
            return $mp_tpl->get();
        }

        // vimeo
        if (ilExternalMediaAnalyzer::isVimeo($this->getFile())) {
            $p = ilExternalMediaAnalyzer::extractVimeoParameters($this->getFile());

            $html = '<iframe src="http://player.vimeo.com/video/' . $p["id"] . '" width="320" height="240" ' .
                'frameborder="0"></iframe>';

            return $html;
        }

        $mimeType = $this->mimeType == "" ? ilObjMediaObject::getMimeType(basename($this->getFile())) : $this->mimeType;
        include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");
        
        // video tag
        if (in_array($mimeType, array("video/mp4", "video/m4v", "video/rtmp",
            "video/x-flv", "video/webm", "video/youtube", "video/vimeo", "video/ogg"))) {
            if ($mimeType == "video/quicktime") {
                $mimeType = "video/mov";
            }
            
            $mp_tpl = new ilTemplate("tpl.flv_player.html", true, true, "Services/MediaObjects");
            
            // preview
            if ($a_preview) {
                if ($this->getDownloadLink() != "") {
                    $mp_tpl->setCurrentBlock("download");
                    $mp_tpl->setVariable("TXT_DOWNLOAD", $lng->txt("download"));
                    $mp_tpl->setVariable("HREF_DOWNLOAD", $this->getDownloadLink());
                    $mp_tpl->parseCurrentBlock();
                }

                $mp_tpl->setCurrentBlock("preview");
                if ($this->getVideoPreviewPic() != "") {
                    $mp_tpl->setVariable("IMG_SRC", $this->getVideoPreviewPic());
                } else {
                    $mp_tpl->setVariable("IMG_SRC", ilUtil::getImagePath("mcst_preview.svg"));
                }
                $mp_tpl->setVariable("IMG_ALT", $this->video_preview_pic_alt);
                $mp_tpl->setVariable("PTITLE", $this->getTitle());
                $mp_tpl->parseCurrentBlock();
            }
            
            // sources
            $mp_tpl->setCurrentBlock("source");
            $mp_tpl->setVariable("FILE", $this->getFile());
            $mp_tpl->setVariable("MIME", $mimeType);
            $mp_tpl->parseCurrentBlock();

            if (in_array($this->getAlternativeVideoMimeType(), array("video/mp4", "video/webm"))) {
                $mp_tpl->setCurrentBlock("source");
                $mp_tpl->setVariable("FILE", $this->getAlternativeVideoFile());
                $mp_tpl->setVariable("MIME", $this->getAlternativeVideoMimeType());
                $mp_tpl->parseCurrentBlock();
            }
            
            $mp_tpl->setCurrentBlock("mejs_video");
            
            if ($a_preview) {
                $mp_tpl->setVariable("CLASS", "ilNoDisplay");
            }
            
            $mp_tpl->setVariable("PLAYER_NR", $this->id . "_" . $this->current_nr);
            $mp_tpl->setVariable("EVENT_URL", $this->event_callback_url);
            $height = $this->getDisplayHeight();
            $width = $this->getDisplayWidth();
            if (is_int(strpos($mimeType, "audio/mpeg"))) {
                $height = "30";
            }

            $mp_tpl->setVariable("DISPLAY_HEIGHT", $height);
            $mp_tpl->setVariable("DISPLAY_WIDTH", $width);
            $mp_tpl->setVariable("PREVIEW_PIC", $this->getVideoPreviewPic());
            $mp_tpl->setVariable("SWF_FILE", ilPlayerUtil::getFlashVideoPlayerFilename(true));
            $mp_tpl->setVariable("FFILE", $this->getFile());
            $mp_tpl->setVariable("TITLE", $this->getTitle());
            $mp_tpl->setVariable("DESCRIPTION", $this->getDescription());
            include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
            if ($a_preview) {
                $mp_tpl->setVariable("CLOSE", ilGlyphGUI::get(ilGlyphGUI::CLOSE));
            }
            $mp_tpl->parseCurrentBlock();
            $r = $mp_tpl->get();

            if (!$a_preview) {
                $tpl->addOnLoadCode("new MediaElementPlayer('#player_" . $this->id . "_" . $this->current_nr . "');");
            }

            //echo htmlentities($r); exit;
            return $r;
        }

        // audio/mpeg
        if (is_int(strpos($mimeType, "audio/mpeg")) ||
            in_array($mimeType, array("application/ogg", "audio/ogg"))) {
            ilPlayerUtil::initMediaElementJs();
            $mp_tpl = new ilTemplate("tpl.flv_player.html", true, true, "Services/MediaObjects");
            $preview_output = false;
            if ($this->getVideoPreviewPic() != "" || $this->getForceAudioPreview()) {
                if ($this->getDownloadLink() != "") {
                    $mp_tpl->setCurrentBlock("adownload");
                    $mp_tpl->setVariable("TXT_DOWNLOAD", $lng->txt("download"));
                    $mp_tpl->setVariable("HREF_DOWNLOAD", $this->getDownloadLink());
                    $mp_tpl->parseCurrentBlock();
                }

                $mp_tpl->setCurrentBlock("apreview");
                if ($this->getVideoPreviewPic() != "") {
                    $mp_tpl->setVariable("IMG_SRC", $this->getVideoPreviewPic());
                } else {
                    $mp_tpl->setVariable("IMG_SRC", ilUtil::getImagePath("mcst_preview.svg"));
                }
                $mp_tpl->setVariable("PTITLE", $this->getTitle());
                $mp_tpl->parseCurrentBlock();
                $preview_output = true;
            }
            $mp_tpl->setCurrentBlock("audio");
            if ($preview_output) {
                $mp_tpl->setVariable("ASTYLE", "margin-top:-30px");
            }
            $mp_tpl->setVariable("AFILE", $this->getFile());
            $mp_tpl->setVariable("APLAYER_NR", $this->id . "_" . $this->current_nr);
            $mp_tpl->setVariable("AEVENT_URL", $this->event_callback_url);
            $mp_tpl->setVariable("AHEIGHT", "30");
            $mp_tpl->setVariable("AWIDTH", "320");
            $mp_tpl->parseCurrentBlock();
            return $mp_tpl->get();
        }

        // images
        if (is_int(strpos($mimeType, "image/"))) {
            $mp_tpl = new ilTemplate("tpl.flv_player.html", true, true, "Services/MediaObjects");

            if ($this->getDownloadLink() != "") {
                $mp_tpl->setCurrentBlock("idownload");
                $mp_tpl->setVariable("TXT_DOWNLOAD", $lng->txt("download"));
                $mp_tpl->setVariable("HREF_DOWNLOAD", $this->getDownloadLink());
                $mp_tpl->parseCurrentBlock();
            }

            $mp_tpl->setCurrentBlock("ipreview");
            if ($this->getVideoPreviewPic() != "") {
                $mp_tpl->setVariable("IMG_SRC", $this->getVideoPreviewPic());
            } else {
                $mp_tpl->setVariable("IMG_SRC", $this->getFile());
            }
            $mp_tpl->setVariable("PTITLE", $this->getTitle());
            $mp_tpl->parseCurrentBlock();

            $mp_tpl->setCurrentBlock("image");
            $mp_tpl->setVariable("IFILE", $this->getFile());
            $mp_tpl->setVariable("IPLAYER_NR", $this->id . "_" . $this->current_nr);
            $mp_tpl->setVariable("ITITLE", $this->getTitle());
            $mp_tpl->setVariable("IDESCRIPTION", $this->getDescription());
            include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
            $mp_tpl->setVariable("ICLOSE", ilGlyphGUI::get(ilGlyphGUI::CLOSE));
            
            if ($this->event_callback_url) {
                $mp_tpl->setVariable("IMG_CALLBACK_URL", $this->event_callback_url);
                $mp_tpl->setVariable("IMG_CALLBACK_PLAYER_NR", $this->id . "_" . $this->current_nr);
            }
            
            $mp_tpl->setVariable("IHEIGHT", $this->getDisplayHeight());
            $mp_tpl->setVariable("IWIDTH", $this->getDisplayWidth());
            $mp_tpl->parseCurrentBlock();
            
            return $mp_tpl->get();
        }
        
        // fallback, no preview mode
        $mimeType = $this->mimeType == "" ? ilObjMediaObject::getMimeType(basename($this->getFile())) : $this->mimeType;
        if (strpos($mimeType, "flv") === false
         && strpos($mimeType, "audio/mpeg") === false
         && strpos($mimeType, "image/png") === false
         && strpos($mimeType, "image/gif") === false) {
            $html = '<embed src="' . $this->getFile() . '" ' .
                    'type="' . $mimeType . '" ' .
                    'ShowControls="1" ' .
                    'autoplay="false" autostart="false" ' .
                    'width="320" height="240" scale="aspect" ></embed>';
            return $html;
        }

        return;
        $tpl->addJavaScript("./Services/MediaObjects/flash_flv_player/swfobject.js");
        $mp_tpl = new ilTemplate("tpl.flv_player.html", true, true, "Services/MediaObjects");
        $mp_tpl->setCurrentBlock("default");
        $mp_tpl->setVariable("FILE", urlencode($this->getFile()));
        $mp_tpl->setVariable("PLAYER_NR", $this->current_nr);
        $mp_tpl->setVariable("DISPLAY_HEIGHT", strpos($mimeType, "audio/mpeg") === false ? "240" : "20");
        $mp_tpl->setVariable("DISPLAY_WIDTH", "320");
        $mp_tpl->parseCurrentBlock();
        return $mp_tpl->get();
    }
    
    /**
     * Get preview html
     *
     * @return string html
     */
    public function getPreviewHtml()
    {
        return $this->getMp3PlayerHtml(true);
    }

    /**
     * Get HTML (no preview) for media player integration
     *
     * @return string html
     */
    public function getMediaPlayerHtml()
    {
        return $this->getMp3PlayerHtml(false);
    }
}
