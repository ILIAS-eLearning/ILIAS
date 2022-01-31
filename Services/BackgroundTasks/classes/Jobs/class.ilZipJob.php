<?php
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilZipJob extends AbstractJob
{
    /**
     * @var null
     */
    private $logger;


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
                new SingleType(StringValue::class),
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
