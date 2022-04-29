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

namespace ILIAS\MediaCast\Video;

/**
 * Video preview UI
 * @author Alexander Killing <killing@leifos.de>
 */
class VideoPreviewGUI
{
    protected \ILIAS\DI\UIServices $ui;
    protected string $file = "";
    protected string $onclick = "";
    protected string $playing_time = "";
    protected \ilLanguage $lng;

    public function __construct(
        string $file,
        string $onclick,
        string $playing_time
    ) {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->file = $file;
        $this->onclick = $onclick;
        $this->playing_time = $playing_time;
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("mcst");
    }

    /**
     * @throws \ilTemplateException
     */
    public function render() : string
    {
        $lng = $this->lng;

        $tpl = new \ilTemplate("tpl.video_preview.html", true, true, "Modules/MediaCast/Video");
        $im = $this->ui->factory()->image()->responsive($this->file, "");
        $tpl->setVariable("IMAGE", $this->ui->renderer()->render($im));
        $tpl->setVariable("ONCLICK", $this->onclick);
        $tpl->setVariable("PLAYING_TIME", $this->playing_time);
        $tpl->setVariable("WATCHED", $lng->txt("mcst_watched"));

        return $tpl->get();
    }
}
