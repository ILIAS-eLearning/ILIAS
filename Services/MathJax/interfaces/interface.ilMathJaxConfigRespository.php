<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

interface ilMathJaxConfigRespository
{
    /**
     * Get the MathJax Configuration
     * @return ilMathJaxConfig
     */
    public function getConfig() : ilMathJaxConfig;

    /**
     * Update the MathJax Configuration
     * @param ilMathJaxConfig $config
     */
    public function updateConfig(ilMathJaxConfig $config);
}