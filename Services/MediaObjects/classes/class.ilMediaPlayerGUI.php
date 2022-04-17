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
 * User interface for media player. Wraps flash mp3 player and similar tools.
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaPlayerGUI
{
    protected bool $force_audio_preview = false;
    protected string $video_preview_pic_alt = "";
    protected string $video_preview_pic = "";
    protected string $alt_video_mime = "";
    protected string $alt_video_file = "";
    protected string $id = "";
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected string $file = "";
    protected int $displayHeight = 0;
    protected int $displayWidth = 0;
    protected string $mimeType = "";
    protected static int $nr = 1;
    protected static bool $lightbox_initialized = false;
    protected int $current_nr = 0;
    protected string $title = "";
    protected string $description = "";
    protected string $event_callback_url = "";
    protected string $download_link = "";

    public function __construct(
        string $a_id = "",
        string $a_event_callback_url = ""
    ) {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->id = $a_id;
        $this->event_callback_url = $a_event_callback_url;
        $this->current_nr = self::$nr;
        self::$nr++;
    }

    public function setFile(
        string $a_file
    ) : void {
        $this->file = $a_file;
    }

    public function getFile() : string
    {
        return $this->file;
    }

    public function setAlternativeVideoFile(
        string $a_val
    ) : void {
        $this->alt_video_file = $a_val;
    }

    public function getAlternativeVideoFile() : string
    {
        return $this->alt_video_file;
    }

    /**
     * Set alternative video mime type
     * @param string $a_val alternative video mime type
     */
    public function setAlternativeVideoMimeType(
        string $a_val
    ) : void {
        $this->alt_video_mime = $a_val;
    }

    public function getAlternativeVideoMimeType() : string
    {
        return $this->alt_video_mime;
    }

    public function setDisplayHeight(int $dHeight) : void
    {
        $this->displayHeight = $dHeight;
    }

    public function getDisplayHeight() : int
    {
        return $this->displayHeight;
    }

    public function setDisplayWidth(int $a_val) : void
    {
        $this->displayWidth = $a_val;
    }

    public function getDisplayWidth() : int
    {
        return $this->displayWidth;
    }

    public function setMimeType(string $value) : void
    {
        $this->mimeType = $value;
    }

    /**
     * Set video preview picture
     */
    public function setVideoPreviewPic(
        string $a_val,
        string $a_alt = ""
    ) : void {
        $this->video_preview_pic = $a_val;
        $this->video_preview_pic_alt = $a_alt;
    }

    public function getVideoPreviewPic() : string
    {
        return $this->video_preview_pic;
    }

    public function setTitle(string $a_val) : void
    {
        $this->title = $a_val;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setDescription(string $a_val) : void
    {
        $this->description = $a_val;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * Force audio preview
     */
    public function setForceAudioPreview(bool $a_val) : void
    {
        $this->force_audio_preview = $a_val;
    }

    public function getForceAudioPreview() : bool
    {
        return $this->force_audio_preview;
    }

    public function setDownloadLink(string $a_val) : void
    {
        $this->download_link = $a_val;
    }

    public function getDownloadLink() : string
    {
        return $this->download_link;
    }

    public static function initJavascript(
        ilGlobalTemplateInterface $a_tpl = null
    ) : void {
        global $DIC;

        $tpl = $DIC["tpl"];

        if ($a_tpl == null) {
            $a_tpl = $tpl;
        }

        ilYuiUtil::initConnection();

        $a_tpl->addJavascript("./Services/MediaObjects/js/MediaObjects.js?1");

        ilPlayerUtil::initMediaElementJs($a_tpl);
    }

    /**
     * Get Html for MP3 Player
     */
    public function getMp3PlayerHtml(
        bool $a_preview = false
    ) : string {
        $tpl = $this->tpl;
        $lng = $this->lng;

        self::initJavascript($tpl);

        if (!self::$lightbox_initialized && $a_preview) {
            $lb = new ilLightboxGUI("media_lightbox");
            $lb->setWidth("660px");
            $lb->addLightbox();
            self::$lightbox_initialized = true;
        }

        // youtube
        if (ilExternalMediaAnalyzer::isYouTube($this->getFile())) {
            $p = ilExternalMediaAnalyzer::extractYouTubeParameters($this->getFile());
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
            $mp_tpl->setVariable("SRC", "https://www.youtube.com/embed/" . $p["v"]);
            $mp_tpl->setVariable("PLAYER_NR", $this->id . "_" . $this->current_nr);
            $mp_tpl->setVariable("TXT_PLAY", $lng->txt("mob_play"));
            $mp_tpl->setVariable("TITLE", $this->getTitle());
            $mp_tpl->setVariable("DESCRIPTION", $this->getDescription());
            if ($a_preview) {
                $mp_tpl->setVariable("CLOSE", ilGlyphGUI::get(ilGlyphGUI::CLOSE));
            }
            $mp_tpl->parseCurrentBlock();
            return $mp_tpl->get();
        }

        // vimeo
        if (ilExternalMediaAnalyzer::isVimeo($this->getFile())) {
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
            $mp_tpl->setVariable("SRC", $this->getFile() . "?controls=0");
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

        $mimeType = $this->mimeType == "" ? ilObjMediaObject::getMimeType(basename($this->getFile())) : $this->mimeType;

        // video tag
        if (in_array($mimeType, array("video/mp4",
                                      "video/m4v",
                                      "video/rtmp",
                                      "video/x-flv",
                                      "video/webm",
                                      "video/youtube",
                                      "video/vimeo",
                                      "video/ogg"
        ))) {
            $style = "";
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
            $mp_tpl->setCurrentBlock("mejs_video");

            if ($a_preview) {
                $mp_tpl->setVariable("WRAP_CLASS", "ilNoDisplay");
                $mp_tpl->setVariable("CLASS", "mejs__player ilNoDisplay");
            }

            // sources
            $mp_tpl->setVariable("FILE", $this->getFile());
            $player_nr = $this->id . "_" . $this->current_nr;
            $mp_tpl->setVariable("PLAYER_NR", $player_nr);
            $mp_tpl->setVariable("TXT_PLAY", $lng->txt("mob_play"));

            $onload_code = "il.MediaObjects.setPlayerConfig('player_" . $player_nr .
                "', {event_url: '" . $this->event_callback_url . "'});";

            $this->tpl->addOnLoadCode(
                $onload_code
            );

            $height = $this->getDisplayHeight();
            $width = $this->getDisplayWidth();
            if (is_int(strpos($mimeType, "audio/mpeg"))) {
                //$height = "30px";
            }

            if ($height != "") {
                $style = "height: " . $height . "; ";
            }
            if ($width != "") {
                $style .= "width: " . $width . "; ";
            }
            if ($style != "") {
                $mp_tpl->setVariable("STYLE", "style='$style'");
            }
            $mp_tpl->setVariable("FILE", $this->getFile());
            $mp_tpl->setVariable("PREVIEW_PIC", $this->getVideoPreviewPic());
            $mp_tpl->setVariable("TITLE", $this->getTitle());
            $mp_tpl->setVariable("DESCRIPTION", $this->getDescription());
            if ($a_preview) {
                $mp_tpl->setVariable("CLOSE", ilGlyphGUI::get(ilGlyphGUI::CLOSE));
            }
            $mp_tpl->parseCurrentBlock();
            $r = $mp_tpl->get();

            if (!$a_preview) {
                $tpl->addOnLoadCode("new MediaElementPlayer('player_" . $this->id . "_" . $this->current_nr . "');");
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
                $mp_tpl->setVariable("ASTYLE", "margin-top:-40px");
            }
            $mp_tpl->setVariable("AFILE", $this->getFile());
            $mp_tpl->setVariable("APLAYER_NR", $this->id . "_" . $this->current_nr);
            $mp_tpl->setVariable("AEVENT_URL", $this->event_callback_url);
            $mp_tpl->setVariable("AHEIGHT", "40");
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

        return "";
    }

    public function getPreviewHtml() : string
    {
        return $this->getMp3PlayerHtml(true);
    }

    /**
     * Get HTML (no preview) for media player integration
     */
    public function getMediaPlayerHtml() : string
    {
        return $this->getMp3PlayerHtml(false);
    }
}
