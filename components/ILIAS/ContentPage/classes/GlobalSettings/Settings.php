<?php

declare(strict_types=1);

namespace ILIAS\ContentPage\GlobalSettings;

/**
 * Class Settings
 * @package ILIAS\ContentPage\GlobalSettings
 * @author Michael Jansen <mjansen@databay.de>
 */
class Settings
{
    protected bool $readingTimeEnabled = false;

    public function isReadingTimeEnabled(): bool
    {
        return $this->readingTimeEnabled;
    }

    public function withEnabledReadingTime(): self
    {
        $clone = clone $this;
        $clone->readingTimeEnabled = true;

        return $clone;
    }

    public function withDisabledReadingTime(): self
    {
        $clone = clone $this;
        $clone->readingTimeEnabled = false;

        return $clone;
    }
}
