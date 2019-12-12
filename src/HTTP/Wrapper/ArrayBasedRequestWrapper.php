<?php declare(strict_types=1);

namespace ILIAS\HTTP\Wrapper;

use ILIAS\Refinery\Transformation;

/**
 * Class ArrayBasedRequestWrapper
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ArrayBasedRequestWrapper implements RequestWrapper
{

    /**
     * @var array
     */
    private $raw_values;


    /**
     * GetRequestWrapper constructor.
     *
     * @param array $raw_values
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
