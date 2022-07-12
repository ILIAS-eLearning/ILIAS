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

namespace ILIAS\MediaCast\BackgroundTasks;

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;

/**
 * Collect files for downloading all media items
 * @author Alexander Killing <killing@leifos.de>
 */
class DownloadAllCollectFilesJob extends AbstractJob
{
    private ?\ilLogger $logger = null;
    protected int $user_id = 0;
    protected int $mcst_id = 0;
    protected int $mcst_ref_id = 0;
    protected \ilLanguage $lng;
    protected string $target_directory = "";
    protected string $temp_dir = "";
    protected string $sanitized_title = "";
    protected \ilObjMediaCast $media_cast;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('mcst');
        $this->logger = $DIC->logger()->mcst();

        $this->sanitized_title = "images";
    }

    public function getInputTypes() : array
    {
        return
            [
                new SingleType(IntegerValue::class),
                new SingleType(IntegerValue::class)
            ];
    }

    public function getOutputType() : \ILIAS\BackgroundTasks\Types\Type
    {
        return new SingleType(StringValue::class);
    }

    public function isStateless() : bool
    {
        return true;
    }

    public function getExpectedTimeOfTaskInSeconds() : int
    {
        return 30;
    }

    public function run(array $input, Observer $observer) : StringValue
    {
        $this->user_id = $input[0]->getValue();
        $this->mcst_ref_id = $input[1]->getValue();

        $this->logger->debug("Get Mediacast " . $this->mcst_ref_id);
        $this->media_cast = new \ilObjMediaCast($this->mcst_ref_id);

        $target_dir = $this->createDirectory();

        $this->logger->debug("Collect in " . $target_dir);
        $this->collectMediaFiles($target_dir);
        $this->logger->debug("Finished collecting.");
        
        $out = new StringValue();
        $out->setValue($target_dir);
        return $out;
    }

    protected function createDirectory() : string
    {
        // temp dir
        $this->temp_dir = \ilFileUtils::ilTempnam();

        // target dir
        $path = $this->temp_dir . DIRECTORY_SEPARATOR;
        $this->target_directory = $path . $this->sanitized_title;
        \ilFileUtils::makeDirParents($this->target_directory);

        return $this->target_directory;
    }

    public function collectMediaFiles(string $target_dir) : void
    {
        $cnt = 0;
        foreach ($this->media_cast->getSortedItemsArray() as $item) {
            $mob = new \ilObjMediaObject($item["mob_id"]);
            $med = $mob->getMediaItem("Standard");

            $cnt++;
            $str_cnt = str_pad($cnt, 4, "0", STR_PAD_LEFT);

            if ($med->getLocationType() === "Reference") {
                $resource = $med->getLocation();
                copy($resource, $target_dir . DIRECTORY_SEPARATOR . $str_cnt . basename($resource));
            } else {
                $path_to_file = \ilObjMediaObject::_getDirectory($mob->getId()) . "/" . $med->getLocation();
                copy($path_to_file, $target_dir . DIRECTORY_SEPARATOR . $str_cnt . $med->getLocation());
            }
        }
    }
}
