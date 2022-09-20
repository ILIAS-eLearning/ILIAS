<?php

declare(strict_types=1);

namespace ILIAS\HTTP\Wrapper;

use ILIAS\Refinery\Transformation;

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
 * Interface RequestWrapper
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface RequestWrapper
{
    /**
     *
     * @return mixed
     */
    public function retrieve(string $key, Transformation $transformation);


    public function has(string $key): bool;
}
