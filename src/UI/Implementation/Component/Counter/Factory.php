<?php declare(strict_types=1);

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Counter;

use ILIAS\UI\Component as C;

class Factory implements C\Counter\Factory
{
    /**
     * @inheritdoc
     */
    public function status(int $number) : C\Counter\Counter
    {
        return new Counter(C\Counter\Counter::STATUS, $number);
    }

    /**
     * @inheritdoc
     */
    public function novelty(int $number) : C\Counter\Counter
    {
        return new Counter(C\Counter\Counter::NOVELTY, $number);
    }
}
