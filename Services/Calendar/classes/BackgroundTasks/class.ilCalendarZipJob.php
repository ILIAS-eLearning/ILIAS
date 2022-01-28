<?php declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Observer;

/**
 * Description of class class
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCalendarZipJob extends AbstractJob
{
    private ilLogger $logger;

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
    public function getInputTypes() : array
    {
        return
            [
                new SingleType(StringValue::class)
            ];
    }

    /**
     * @inheritDoc
     */
    public function getOutputType() : Type
    {
        return new SingleType(StringValue::class);
    }

    /**
     * @inheritDoc
     */
    public function isStateless() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     * @todo use filsystem service
     */
    public function run(array $input, Observer $observer) : Value
    {
        $this->logger->debug('Start zipping input dir!');
        $tmpdir = $input[0]->getValue();
        $this->logger->debug('Zipping directory:' . $tmpdir);

        ilFileUtils::zip($tmpdir, $tmpdir . '.zip');

        // delete temp directory
        ilFileUtils::delDir($tmpdir);

        $zip_file_name = new StringValue();
        $zip_file_name->setValue($tmpdir . '.zip');
        return $zip_file_name;
    }

    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds() : int
    {
        return 30;
    }
}
