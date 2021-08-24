<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilStrictCliCronManager
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilStrictCliCronManager implements \ilCronManagerInterface
{
    protected ilCronManagerInterface $cronManager;

    public function __construct(ilCronManagerInterface $cronManager)
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

    public function runActiveJobs() : void
    {
        if (in_array(PHP_SAPI, array_map('strtolower', $this->getValidPhpApis()), true)) {
            $this->cronManager->runActiveJobs();
        }
    }
}
