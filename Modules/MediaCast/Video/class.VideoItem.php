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
 * @author Alexander Killing <killing@leifos.de>
 */
class VideoItem
{
    protected string $id = "";
    protected string $title = "";
    protected int $time = 0;
    protected string $mime = "";
    protected string $resource = "";
    protected string $preview_pic = "";
    protected string $description = "";
    protected string $playing_time = "";
    protected int $duration = 0;

    public function __construct(
        string $id,
        string $title,
        int $time,
        string $mime,
        string $resource,
        string $preview_pic,
        string $description = "",
        string $playing_time = "",
        int $duration = 0
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->time = $time;
        $this->mime = $mime;
        $this->resource = $resource;
        $this->preview_pic = $preview_pic;
        if ($this->preview_pic == "") {
            $this->preview_pic = \ilUtil::getImagePath("mcst_preview.svg");
        }
        $this->description = $description;
        $this->playing_time = $playing_time;
        $this->duration = $duration;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getPreviewPic(): string
    {
        return $this->preview_pic;
    }

    public function getPlayingTime(): string
    {
        return $this->playing_time;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }
}
