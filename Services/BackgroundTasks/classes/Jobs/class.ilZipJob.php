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

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilZipJob extends AbstractJob
{
    private ?ilLogger $logger;


    /**
     * Construct
     */
    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->cal();
    }


    /**
     * @inheritDoc
     */
    public function getInputTypes(): array
    {
        return
            [
                new SingleType(StringValue::class),
            ];
    }


    /**
     * @inheritDoc
     */
    public function getOutputType(): Type
    {
        return new SingleType(StringValue::class);
    }


    /**
     * @inheritDoc
     */
    public function isStateless(): bool
    {
        return true;
    }


    /**
     * @inheritDoc
     * @todo use filsystem service
     */
    public function run(array $input, \ILIAS\BackgroundTasks\Observer $observer): Value
    {
        $this->logger->debug('Start zipping input dir!');
        $this->logger->dump($input);
        $tmpdir = rtrim($input[0]->getValue(), "/");
        $this->logger->debug('Zipping directory:' . $tmpdir);
        $zip_name = $tmpdir . '.zip';
        ilFileUtils::zip($tmpdir, $zip_name);

        // delete temp directory
        ilFileUtils::delDir($tmpdir);

        $zip_file_name = new StringValue();
        $zip_file_name->setValue($zip_name);

        return $zip_file_name;
    }


    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds(): int
    {
        return 30;
    }
}
