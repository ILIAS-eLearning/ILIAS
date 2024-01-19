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

class Factory implements I\Factory
{
    public function text(string $title): I\Text
    {
        return new Text($title);
    }

    public function number(string $title): I\Number
    {
        return new Number($title);
    }

    public function date(string $title, \ILIAS\Data\DateFormat\DateFormat $format): I\Date
    {
        return new Date($title, $format);
    }

    public function status(string $title): I\Status
    {
        return new Status($title);
    }

    public function statusIcon(string $title): I\StatusIcon
    {
        return new StatusIcon($title);
    }

    public function boolean(string $title, string $true, string $false): I\Boolean
    {
        return new Boolean($title, $true, $false);
    }

    public function eMail(string $title): I\EMail
    {
        return new EMail($title);
    }

    public function timeSpan(string $title, \ILIAS\Data\DateFormat\DateFormat $format): I\TimeSpan
    {
        return new TimeSpan($title, $format);
    }

    public function link(string $title): I\Link
    {
        return new Link($title);
    }

    public function linkListing(string $title): I\LinkListing
    {
        return new LinkListing($title);
    }
}
