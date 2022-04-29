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

namespace ILIAS\MediaCast\Presentation;

use ILIAS\MediaCast\Video\VideoWidgetGUI;
use ILIAS\MediaCast\Video\VideoSequence;
use ILIAS\MediaCast\Video\VideoPreviewGUI;
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class VideoViewGUI
{
    protected \ilToolbarGUI $toolbar;
    protected \ilGlobalTemplateInterface $main_tpl;
    protected \ilObjMediaCast $media_cast;
    protected \ilGlobalTemplateInterface $tpl;
    protected \ILIAS\DI\UIServices $ui;
    protected \ilLanguage $lng;
    protected \ilObjUser $user;
    protected string $completed_callback = "";
    protected string $autoplay_callback = "";
    protected VideoSequence $video_sequence;
    protected string $video_wrapper_id = "mcst_video";

    public function __construct(\ilObjMediaCast $obj, \ilGlobalTemplateInterface $tpl = null)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->media_cast = $obj;
        $this->tpl = $tpl;
        $this->video_sequence = new VideoSequence($this->media_cast);
        $this->user = $DIC->user();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
    }

    public function setCompletedCallback(string $completed_callback) : void
    {
        $this->completed_callback = $completed_callback;
    }

    public function setAutoplayCallback(string $autoplay_callback) : void
    {
        $this->autoplay_callback = $autoplay_callback;
    }

    /**
     * @throws \ilTemplateException
     */
    public function renderMainColumn() : string
    {
        if (count($this->video_sequence->getVideos()) == 0) {
            return "";
        }

        $widget = new VideoWidgetGUI($this->tpl, "mcst_video");
        $widget->setVideo($this->video_sequence->getFirst());
        return $widget->render();
    }

    public function renderToolbar() : void
    {
        $toolbar = $this->toolbar;
        $lng = $this->lng;

        $mcst_settings = \ilMediaCastSettings::_getInstance();

        $autoplay = $this->getAutoplay();

        $factory = $this->ui->factory();
        $renderer = $this->ui->renderer();

        $back = $factory->button()->standard("<span class=\"glyphicon glyphicon-chevron-left \" aria-hidden=\"true\"></span>", "")
                  ->withOnLoadCode(function ($id) {
                      return
                          "$(\"#$id\").click(function() { il.VideoWidget.previous(\"" . $this->video_wrapper_id . "\"); return false;});";
                  });
        $next = $factory->button()->standard("<span class=\"glyphicon glyphicon-chevron-right \" aria-hidden=\"true\"></span>", "")
                  ->withOnLoadCode(function ($id) {
                      return
                          "$(\"#$id\").click(function() { il.VideoWidget.next(\"" . $this->video_wrapper_id . "\"); return false;});";
                  });

        $toolbar->addComponent($back);
        $toolbar->addComponent($next);

        // autoplay
        if ($this->media_cast->getAutoplayMode() != \ilObjMediaCast::AUTOPLAY_NO) {
            $toolbar->addSeparator();
            $s = new SignalGenerator();
            $autoplay_on = $s->create();
            $autoplay_off = $s->create();
            $button = $factory->button()->toggle($lng->txt("mcst_autoplay"), $autoplay_on, $autoplay_off, $autoplay);
            $toolbar->addComponent($button);
            $this->main_tpl->addOnLoadCode("
                $(document).on('" . $autoplay_on . "', function (event, signalData) {
                    il.VideoPlaylist.autoplay('mcst_playlist', true);
                });
                $(document).on('" . $autoplay_off . "', function (event, signalData) {
                    il.VideoPlaylist.autoplay('mcst_playlist', false);
                });");
        }
    }

    protected function getAutoplay() : bool
    {
        $autoplay = ($this->user->existsPref("mcst_autoplay"))
            ? (bool) $this->user->getPref("mcst_autoplay")
            : ($this->media_cast->getAutoplayMode() == \ilObjMediaCast::AUTOPLAY_ACT);
        if ($this->media_cast->getAutoplayMode() == \ilObjMediaCast::AUTOPLAY_NO) {
            $autoplay = false;
        }
        return $autoplay;
    }

    public function renderSideColumn() : string
    {
        $mcst_settings = \ilMediaCastSettings::_getInstance();

        $autoplay = $this->getAutoplay();

        $lng = $this->lng;
        $tpl = new \ilTemplate("tpl.video_cast_side.html", true, true, "Modules/MediaCast/Presentation");

        $factory = $this->ui->factory();
        $renderer = $this->ui->renderer();

        // items
        $items = [];
        $has_items = false;

        $panel_items = [];

        foreach ($this->video_sequence->getVideos() as $video) {
            $has_items = true;
            $preview = new VideoPreviewGUI(
                $video->getPreviewPic(),
                "il.VideoPlaylist.loadItem('mcst_playlist', '" . $video->getId() . "', true);",
                $video->getPlayingTime()
            );
            $completed = false;

            $re = \ilChangeEvent::_lookupReadEvents($video->getId(), $this->user->getId());
            if (count($re) > 0) {
                if ($re[0]["read_count"] > 0) {
                    $completed = true;
                }
            }

            $b = $factory->button()->shy($video->getTitle(), "")->withOnLoadCode(function ($id) use ($video) {
                return
                    "$(\"#$id\").click(function() { il.VideoPlaylist.loadItem('mcst_playlist', '" . $video->getId() . "', true); return false;});";
            });

            $items[] = [
                "id" => $video->getId(),
                "resource" => $video->getResource(),
                "preview" => $preview->render(),
                "preview_pic" => $video->getPreviewPic(),
                "title" => $video->getTitle(),
                "linked_title" => $renderer->renderAsync($b),
                "mime" => $video->getMime(),
                "poster" => $video->getPreviewPic(),
                "description" => nl2br($video->getDescription()),
                "completed" => $completed,
                "duration" => $video->getDuration()
            ];
        }

        $panel = $factory->panel()->secondary()->listing(
            "Videos",
            []
        );
        $panel_html = $renderer->render($panel);
        $panel_html = str_replace(
            '<div class="panel-body">',
            '<div class="panel-body"><div id="mcst_playlist"></div>',
            $panel_html,
        );

        $tpl->setVariable("PANEL", $panel_html);

        // previous items / next items links
        if ($has_items) {
            $tpl->setVariable(
                "PREV",
                $renderer->render(
                    $factory->button()->standard($lng->txt("mcst_prev_items"), "")->withOnLoadCode(
                        function ($id) {
                            return
                                "$(\"#$id\").click(function() { il.VideoPlaylist.previousItems('mcst_playlist'); return false;});";
                        }
                    )
                )
            );
            $tpl->setVariable(
                "NEXT",
                $renderer->render(
                    $factory->button()->standard($lng->txt("mcst_next_items"), "")->withOnLoadCode(
                        function ($id) {
                            return
                                "$(\"#$id\").click(function() { il.VideoPlaylist.nextItems('mcst_playlist'); return false;});";
                        }
                    )
                )
            );

            $item_tpl = new \ilTemplate("tpl.playlist_item.html", true, true, "Modules/MediaCast/Video");
            $item_tpl->setVariable("TITLE", " ");
            $item_content = str_replace("\n", "", $item_tpl->get());

            $item = $factory->item()->standard("#video-title#")
                    ->withLeadImage(
                        $factory->image()->responsive("#img-src#", "#img-alt#")
                    );

            $item_content = $renderer->render($item);
            $item_content = str_replace("\n", "", $item_content);

            $this->tpl->addOnLoadCode(
                "il.VideoPlaylist.init('mcst_playlist', 'mcst_video', " . json_encode(
                    $items
                ) . ", '$item_content', " . ($autoplay ? "true" : "false") . ", " .
                (int) $this->media_cast->getNumberInitialVideos(
                ) . ", '" . $this->completed_callback . "', '" . $this->autoplay_callback . "', " . ((int) $mcst_settings->getVideoCompletionThreshold()) . ");"
            );

            if (count($items) === 1) {
                return "";
            }

            return $tpl->get();
        }

        return "";
    }

    /**
     * @throws \ilTemplateException
     */
    public function render() : string
    {
        // this is current only to include the resize mechanism when
        // the main menu is changed, so that the player is resized, too
        \ilMediaPlayerGUI::initJavascript();
        $tpl = new \ilTemplate("tpl.video_cast_layout.html", true, true, "Modules/MediaCast/Presentation");
        $side_column = $this->renderSideColumn();


        if ($side_column != "") {
            $tpl->setCurrentBlock("with_side_column");
            $tpl->setVariable("SIDE", $side_column);
        } else {
            $tpl->setCurrentBlock("video_only");
        }
        $tpl->setVariable("MAIN", $this->renderMainColumn());
        $tpl->parseCurrentBlock();
        return $tpl->get();
    }

    public function show() : void
    {
        if (is_object($this->tpl)) {
            $this->renderToolbar();
            $this->tpl->setContent($this->renderMainColumn());
            $this->tpl->setRightContent($this->renderSideColumn());
        }
    }
}
