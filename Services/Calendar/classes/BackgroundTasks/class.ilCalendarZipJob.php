<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

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
    public function run(array $input, \ILIAS\BackgroundTasks\Observer $observer) : Value
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
    public function getExpectedTimeOfTaskInSeconds() : int
    {
        return 30;
    }
}
