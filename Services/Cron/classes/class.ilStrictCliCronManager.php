<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilStrictCliCronManager
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilStrictCliCronManager implements \ilCronManagerInterface
{
    /**
     * @var \ilCronManagerInterface
     */
    protected $cronManager;

    /**
     * ilStrictCliCronManager constructor.
     * @param ilCronManagerInterface $cronManager
     */
    public function __construct(\ilCronManagerInterface $cronManager)
    {
        $this->cronManager = $cronManager;
    }

    /**
     * @return string[]
     */
    private function getValidPhpApis() : array
    {
        return [
            'cli'
        ];
    }

    /**
     * @inheritdoc
     */
    public function runActiveJobs()
    {
        if (in_array(php_sapi_name(), array_map('strtolower', $this->getValidPhpApis()))) {
            $this->cronManager->runActiveJobs();
        }
    }
}
