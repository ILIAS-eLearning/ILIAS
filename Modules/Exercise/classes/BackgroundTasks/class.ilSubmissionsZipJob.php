<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Observer;

/**
 * Description of class class
 *
 * @author Jesús López <lopez@leifos.com>
 *
 */
class ilSubmissionsZipJob extends AbstractJob
{
    protected ilLogger $logger;
    
    public function __construct()
    {
        $this->logger = $GLOBALS['DIC']->logger()->exc();
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

    /**
     * @todo use filesystem service
     * @param array    $input
     * @param Observer $observer
     * @return StringValue
     * @throws \ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException
     */
    public function run(
        array $input,
        Observer $observer
    ) : StringValue {
        $tmpdir = $input[0]->getValue();

        ilUtil::zip($tmpdir, $tmpdir . '.zip');
        
        // delete temp directory
        ilUtil::delDir($tmpdir);

        $zip_file_name = new StringValue();
        $zip_file_name->setValue($tmpdir . '.zip');
        return $zip_file_name;
    }

    public function getExpectedTimeOfTaskInSeconds() : int
    {
        return 30;
    }
}
