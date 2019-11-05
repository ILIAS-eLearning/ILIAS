<?php
/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Data\Factory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveApplyToFromTransform;

/**
 * Transform a string representing a datetime-value to php's DateTimeImmutable
 * see https://www.php.net/manual/de/datetime.formats.php
 */
class DateTimeTransformation implements Transformation
{
    use DeriveApplyToFromTransform;

    /**
     * @var DataFactory
     */
    private $factory;

    /**
     * @param Factory $factory
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        try {
            return new \DateTimeImmutable($from);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage(), 1);
        }
    }

    /**
     * @inheritdoc
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }
}
