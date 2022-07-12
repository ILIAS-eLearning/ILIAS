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

use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;

/**
 * Zip media files
 * @author Alexander Killing <killing@leifos.de>
 */
class DownloadAllZipJob extends AbstractJob
{
    private ?\ilLogger $logger = null;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->mcst();
    }

    public function getInputTypes() : array
    {
        return
            [
                new SingleType(StringValue::class)
            ];
    }

    public function getOutputType() : SingleType
    {
        return new SingleType(StringValue::class);
    }

    public function isStateless() : bool
    {
        return true;
    }

    public function run(array $input, \ILIAS\BackgroundTasks\Observer $observer) : StringValue
    {
        $tmpdir = $input[0]->getValue();

        $this->logger->debug("Zip $tmpdir into " . $tmpdir . '.zip');

        \ilFileUtils::zip($tmpdir, $tmpdir . '.zip');

        // delete temp directory
        \ilFileUtils::delDir($tmpdir);

        $zip_file_name = new StringValue();
        $zip_file_name->setValue($tmpdir . '.zip');

        $this->logger->debug("Returning " . $tmpdir . '.zip');

        return $zip_file_name;
    }

    public function getExpectedTimeOfTaskInSeconds() : int
    {
        return 30;
    }
}
