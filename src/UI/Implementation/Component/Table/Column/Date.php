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

    public function __construct(string $title, \ILIAS\Data\DateFormat $format)
    {
        $this->format = $format;
        parent::__construct($title);
    }

    public function getType() : string
    {
        return self::COLUMN_TYPE_DATE;
    }

    public function getFormat() : \ILIAS\Data\DateFormat
    {
        return $this->format;
    }
}