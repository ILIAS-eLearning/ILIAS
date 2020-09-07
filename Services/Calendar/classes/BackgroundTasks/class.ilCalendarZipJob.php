<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilCalendarZipJob extends AbstractJob
{
    private $logger = null;
    
    
    /**
     * Construct
     */
    public function __construct()
    {
        $this->logger = $GLOBALS['DIC']->logger()->cal();
    }
    
    /**
     * @inheritDoc
     */
    public function getInputTypes()
    {
        return
        [
            new SingleType(StringValue::class)
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOutputType()
    {
        return new SingleType(StringValue::class);
    }

    /**
     * @inheritDoc
     */
    public function isStateless()
    {
        return true;
    }

    /**
     * @inheritDoc
     * @todo use filsystem service
     */
    public function run(array $input, \ILIAS\BackgroundTasks\Observer $observer)
    {
        $this->logger->debug('Start zipping input dir!');
        $this->logger->dump($input);
        $tmpdir = $input[0]->getValue();
        $this->logger->debug('Zipping directory:' . $tmpdir);
        
        ilUtil::zip($tmpdir, $tmpdir . '.zip');
        
        // delete temp directory
        ilUtil::delDir($tmpdir);

        $zip_file_name = new StringValue();
        $zip_file_name->setValue($tmpdir . '.zip');
        return $zip_file_name;
    }

    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds()
    {
        return 30;
    }
}
