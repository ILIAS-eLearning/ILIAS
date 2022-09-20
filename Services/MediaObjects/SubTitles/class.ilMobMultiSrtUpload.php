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
 * Handler class for multi srt upload
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilMobMultiSrtUpload
{
    protected ilMobMultiSrtInt $multi_srt;
    protected ilLanguage $lng;

    /**
     * @param ilMobMultiSrtInt $a_multi_srt adapter implementation
     */
    public function __construct(
        ilMobMultiSrtInt $a_multi_srt
    ) {
        global $DIC;

        $lng = $DIC->language();

        $this->lng = $lng;
        $this->multi_srt = $a_multi_srt;
    }

    /**
     * Get directory for multi srt upload
     */
    public function getMultiSrtUploadDir(): string
    {
        return $this->multi_srt->getUploadDir();
    }


    /**
     * Upload multi srt file
     *
     * @param array $a_file file info array
     * @throws ilException
     * @throws ilMobSrtUploadException
     */
    public function uploadMultipleSubtitleFile(
        array $a_file
    ): void {
        if (!is_file($a_file["tmp_name"])) {
            throw new ilMobSrtUploadException($this->lng->txt("cont_srt_zip_file_could_not_be_uploaded"));
        }

        $dir = $this->getMultiSrtUploadDir();
        ilFileUtils::delDir($dir, true);
        ilFileUtils::makeDirParents($dir);
        ilFileUtils::moveUploadedFile($a_file["tmp_name"], "multi_srt.zip", $dir . "/" . "multi_srt.zip");
        ilFileUtils::unzip($dir . "/multi_srt.zip", true);
    }

    /**
     * Clear multi feedback directory
     */
    public function clearMultiSrtDirectory(): void
    {
        ilFileUtils::delDir($this->getMultiSrtUploadDir());
    }

    /**
     * Get all srt files of srt multi upload
     */
    public function getMultiSrtFiles(): array
    {
        $items = array();

        $lang_codes = ilMDLanguageItem::_getPossibleLanguageCodes();

        $dir = $this->getMultiSrtUploadDir();
        $files = ilFileUtils::getDir($dir);
        foreach ($files as $k => $i) {
            // check directory
            if ($i["type"] == "file" && !in_array($k, array(".", ".."))) {
                if (pathinfo($k, PATHINFO_EXTENSION) == "srt") {
                    $lang = "";
                    if (substr($k, strlen($k) - 7, 1) == "_") {
                        $lang = substr($k, strlen($k) - 6, 2);
                        if (!in_array($lang, $lang_codes)) {
                            $lang = "";
                        }
                    }
                    $items[] = array("filename" => $k, "lang" => $lang);
                }
            }
        }

        foreach ($this->multi_srt->getMobIds() as $mob) {
            $m = new ilObjMediaObject($mob);
            $mi = $m->getMediaItem("Standard");
            if ($mi->getLocationType() == "LocalFile" && is_int(strpos($mi->getFormat(), "video"))) {
                // $loc is e.g. "echo-hereweare.mp4", we not look for
                // "echo-hereweare_<langcode>.srt" files
                $loc = pathinfo($mi->getLocation(), PATHINFO_FILENAME);
                foreach ($items as $i => $item) {
                    if (substr($item["filename"], 0, strlen($loc)) == $loc &&
                        substr($item["filename"], strlen($loc), 1) == "_" &&
                        pathinfo($item["filename"], PATHINFO_EXTENSION) == "srt") {
                        $l = substr($item["filename"], strlen($loc) + 1, 2);
                        if (in_array($l, $lang_codes)) {
                            $items[$i]["lang"] = $l;
                            $items[$i]["mob"] = $mob;
                            $items[$i]["mob_title"] = $m->getTitle();
                        }
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Move all srt files that could be mapped to media objects
     */
    public function moveMultiSrtFiles(): int
    {
        $items = $this->getMultiSrtFiles();
        $cnt = 0;
        foreach ($items as $i) {
            if ($i["mob"] > 0 && $i["lang"] != "") {
                $mob = new ilObjMediaObject($i["mob"]);
                $mob->uploadSrtFile($this->getMultiSrtUploadDir() . "/" . $i["filename"], $i["lang"], "rename");
                $cnt++;
            }
        }
        return $cnt;
    }
}
