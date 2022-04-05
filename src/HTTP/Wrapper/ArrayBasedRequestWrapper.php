<?php declare(strict_types=1);

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
 * Class ArrayBasedRequestWrapper
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ArrayBasedRequestWrapper implements RequestWrapper
{
    private array $raw_values;


    /**
     * GetRequestWrapper constructor.
     * @param mixed[] $raw_values
     */
    public function __construct(array $raw_values)
    {
        $this->raw_values = $raw_values;
    }


    /**
     * @inheritDoc
     */
    public function retrieve(string $key, Transformation $transformation)
    {
        if (!$this->has($key)) {
            throw new \OutOfBoundsException('unknown property demanded');
        }

        return $transformation->transform($this->raw_values[$key]);
    }


    /**
     * @inheritDoc
     */
    public function has(string $key) : bool
    {
        return isset($this->raw_values[$key]);
    }
}
