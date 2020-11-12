<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\ContentPage\GlobalSettings;

/**
 * Class Settings
 * @package ILIAS\ContentPage\GlobalSettings
 * @author Michael Jansen <mjansen@databay.de>
 */
class Settings
{
    /** @var bool */
    protected $readingTimeEnabled = false;

    /**
     * @return bool
     */
    public function isReadingTimeEnabled() : bool
    {
        return $this->readingTimeEnabled;
    }

    /**
     * @return $this
     */
    public function withEnabledReadingTime() : self
    {
        $clone = clone $this;
        $clone->readingTimeEnabled = true;

        return $clone;
    }

    /**
     * @return $this
     */
    public function withDisabledReadingTime() : self
    {
        $clone = clone $this;
        $clone->readingTimeEnabled = false;

        return $clone;
    }
}
