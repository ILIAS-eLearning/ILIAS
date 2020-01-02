<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Transformation;

/**
 * Factory for basic transformations.
 * For purpose and usage see README.md
 */
class Factory
{
    /**
     * Add labels to an array.
     *
     * Will transform ["a","b"] to ["A" => "a", "B" => "b"] with $labels = ["A", "B"].
     *
     * @param   string[] $labels
     * @return  Transformation
     */
    public function addLabels(array $labels)
    {
        return new Transformations\AddLabels($labels);
    }

    /**
     * Split string at given delimiter.
     *
     * Will transform "a,b,c" to ["a", "b", "c"] with $delim = ",".
     *
     * @param   string $delimiter
     * @return  Transformation
     */
    public function splitString($delimiter)
    {
        return new Transformations\SplitString($delimiter);
    }

    /**
     * Create a custom transformation.
     *
     * @param	callable $f	mixed -> mixed
     * @return  Transformation
     */
    public function custom(callable $f)
    {
        return new Transformations\Custom($f);
    }

    /**
     * Transform primitive value to data-type.
     *
     * @param	string $type
     * @return  Transformation
     */
    public function toData($type)
    {
        return new Transformations\Data($type);
    }
}
