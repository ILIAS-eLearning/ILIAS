<?php

declare(strict_types=1);
/**
 * Abstracts content of a less file. Currently we have Variable, Category and Comment (random content) as instances.
 */
abstract class ilSystemStyleLessItem
{
    abstract public function __toString() : string;
}
