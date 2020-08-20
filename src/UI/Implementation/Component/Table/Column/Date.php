<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as C;

class Date extends Column implements C\Date
{
    /**
     * @var \ILIAS\Data\DateFormat
     */
    protected $format;

    public function __construct(string $title, \ILIAS\Data\DateFormat\DateFormat $format)
    {
        $this->format = $format;
        parent::__construct($title);
    }

    public function getFormat() : \ILIAS\Data\DateFormat\DateFormat
    {
        return $this->format;
    }
}
