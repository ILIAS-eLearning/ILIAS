<?php declare(strict_types=1);

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Counter;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Counter\Counter as Spec;

class Counter implements Spec
{
    use ComponentHelper;

    private static array $types = array( self::NOVELTY, self::STATUS);
    private string $type;
    private int $number;

    public function __construct(string $type, int $number)
    {
        $this->checkArgIsElement("type", $type, self::$types, "counter type");
        $this->type = $type;
        $this->number = $number;
    }

    /**
     * @inheritdoc
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getNumber() : int
    {
        return $this->number;
    }
}
