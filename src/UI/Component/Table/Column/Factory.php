<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nhaagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table\Column;

interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *       The Text Column is used for (short) text.
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Column\Text
     */
    public function text(string $title) : Text;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Number Column is used for numeric values.
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Column\Number
     */
    public function number(string $title) : \ILIAS\UI\Component\Table\Column\Number;
}
