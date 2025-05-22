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

namespace ILIAS\MetaData\Presentation;

use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\Refinery\Factory as Refinery;

class Utilities implements UtilitiesInterface
{
    protected \ilLanguage $lng;
    protected \ilObjUser $user;
    protected Refinery $refinery;

    public function __construct(
        \ilLanguage $lng,
        \ilObjUser $user,
        Refinery $refinery
    ) {
        $this->lng = $lng;
        $this->lng->loadLanguageModule('meta');
        $this->user = $user;
        $this->refinery = $refinery;
    }

    public function getUserDateFormat(): DateFormat
    {
        return $this->user->getDateFormat();
    }

    public function txt(string $key): string
    {
        return $this->lng->txt($key);
    }

    public function txtFill(string $key, string ...$values): string
    {
        if ($this->lng->exists($key)) {
            return sprintf($this->lng->txt($key), ...$values);
        }
        return $key . ' ' . implode(', ', $values);
    }

    public function sanitizeForHTML(string $string): string
    {
        return $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform($string);
    }
}
