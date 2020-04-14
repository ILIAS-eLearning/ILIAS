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
     * rules:
     *   usage:
     *     1: Text Columns SHOULD be used to display short textual information.
     *   style:
     *     1: Text Columns SHOULD NOT exceed one line.
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
     * rules:
     *   usage:
     *     1: Number Columns SHOULD be used to display numeric values.
     *   style:
     *     1: Number Columns MUST NOT have more than one value.
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Column\Number
     */
    public function number(string $title) : Number;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Date Column is used for single dates.
     *
     * rules:
     *   usage:
     *     1: Date Columns SHOULD be used to display dates.
     *   style:
     *     1: Date Columns MUST NOT have more than one value.
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Column\Date
     */
    public function date(string $title, \ILIAS\Data\DateFormat $format) : Date;
}
