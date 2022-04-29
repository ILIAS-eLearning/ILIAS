<?php

namespace ILIAS\LTI\ToolProvider;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class Outcome
{

    /**
     * Language value.
     *
     * @var string $language
     */
    public ?string $language = null;
    /**
     * Outcome status value.
     *
     * @var string $status
     */
    public ?string $status = null;

    /**
     * Outcome date value.
     */
    public ?string $date = null;
    /**
     * Outcome type value.
     *
     * @var string $type
     */
    public ?string $type = null;
    /**
     * Outcome data source value.
     *
     * @var string $dataSource
     */
    public ?string $dataSource = null;

    /**
         * Outcome value.
         */
    private ?string $value = null;

    /**
     * Class constructor.
     * @param string|null $value Outcome value (optional, default is none)
     */
    public function __construct(?string $value = null)
    {
        $this->value = $value;
        $this->language = 'en-US';
        $this->date = gmdate('Y-m-d\TH:i:s\Z', time());
        $this->type = 'decimal';
    }

    /**
     * Get the outcome value.
     *
     * @return string Outcome value
     */
    public function getValue() : ?string
    {
        return $this->value;
    }

    /**
     * Set the outcome value.
     *
     * @param string $value  Outcome value
     */
    public function setValue(string $value) : void
    {
        $this->value = $value;
    }
}
