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
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class VideoSequence
{
    protected \ilObjMediaCast $media_cast;
    /** @var VideoItem[] */
    protected array $videos;

    public function __construct(\ilObjMediaCast $cast)
    {
        $this->media_cast = $cast;
        $this->init();
    }

    protected function init() : void
    {
        $videos = [];
        foreach ($this->media_cast->getSortedItemsArray() as $item) {
            $mob = new \ilObjMediaObject($item["mob_id"]);
            $med = $mob->getMediaItem("Standard");
            $title = $item["title"];
            $time = (int) $item["playtime"];
            $preview_pic = "";
            if ($mob->getVideoPreviewPic() != "") {
                $preview_pic = $mob->getVideoPreviewPic();
            }

            $mime = '';
            $resource = '';

            if (is_object($med)) {
                if (strcasecmp("Reference", $med->getLocationType()) == 0) {
                    $resource = $med->getLocation();
                } else {
                    $path_to_file = \ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
                    $resource = $path_to_file;
                }
                $mime = $med->getFormat();
            }
            if (in_array($mime, ["video/mp4", "video/vimeo"])) {
                if (!is_int(strpos($resource, "?"))) {
                    $resource .= "?controls=0";
                }
            }
            if (in_array($mime, ["video/mp4", "video/vimeo", "video/youtube"])) {
                $videos[] = new VideoItem(
                    $item["mob_id"],
                    $title,
                    $time,
                    $mime,
                    $resource,
                    $preview_pic,
                    (string) $item["content"],
                    (string) $item["playtime"],
                    $med->getDuration()
                );
            }
        }
        $this->videos = $videos;
    }

    /**
     * @return VideoItem[]
     */
    public function getVideos() : array
    {
        return $this->videos;
    }

    public function getFirst() : ?VideoItem
    {
        if (count($this->videos) > 0) {
            return $this->videos[0];
        }
        return null;
    }
}
