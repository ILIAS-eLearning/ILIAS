<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as I;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;

class Factory implements I\Factory
{
    protected \ilLanguage $lng;

    public function __construct(
        \ilLanguage $lng
    ) {
        $this->lng = $lng;
    }

    public function text(string $title): I\Text
    {
        return new Text($this->lng, $title);
    }

    public function number(
        string $title
    ): I\Number {
        return new Number($this->lng, $title);
    }

    public function date(string $title, \ILIAS\Data\DateFormat\DateFormat $format): I\Date
    {
        return new Date($this->lng, $title, $format);
    }

    public function status(string $title): I\Status
    {
        return new Status($this->lng, $title);
    }

    public function statusIcon(string $title): I\StatusIcon
    {
        return new StatusIcon($this->lng, $title);
    }

    public function boolean(
        string $title,
        $true,
        $false
    ): I\Boolean {
        assert(is_string($true) || $true instanceof Icon || $true instanceof Glyph);
        assert(is_string($false) || $false instanceof Icon || $false instanceof Glyph);
        return new Boolean($this->lng, $title, $true, $false);
    }

    public function eMail(string $title): I\EMail
    {
        return new EMail($this->lng, $title);
    }

    public function timeSpan(string $title, \ILIAS\Data\DateFormat\DateFormat $format): I\TimeSpan
    {
        return new TimeSpan($this->lng, $title, $format);
    }

    public function link(string $title): I\Link
    {
        return new Link($this->lng, $title);
    }

    public function linkListing(string $title): I\LinkListing
    {
        return new LinkListing($this->lng, $title);
    }
}
