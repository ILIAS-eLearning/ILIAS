<?php declare(strict_types=1);

namespace ILIAS\HTTP\Wrapper;

use ILIAS\Refinery\Transformation;

/**
 * Interface RequestWrapper
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface RequestWrapper
{

    /**
     * @param string         $key
     * @param Transformation $transformation
     *
     * @return mixed
     */
    public function retrieve(string $key, Transformation $transformation);


    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key) : bool;
}
