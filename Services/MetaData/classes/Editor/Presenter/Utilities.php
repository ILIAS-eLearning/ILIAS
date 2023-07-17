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

namespace ILIAS\MetaData\Editor\Presenter;

use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;

class Utilities implements UtilitiesInterface
{
    protected PresentationUtilities $utilities;

    public function __construct(
        PresentationUtilities $utilities,
    ) {
        $this->utilities = $utilities;
    }

    public function shortenString(
        string $string,
        int $max_length
    ): string {
        if (function_exists('mb_substr')) {
            return mb_substr($string, 0, $max_length, 'UTF-8');
        } else {
            return substr($string, 0, $max_length);
        }
    }

    public function getUserDateFormat(): DateFormat
    {
        return $this->utilities->getUserDateFormat();
    }

    public function txt(string $key): string
    {
        return $this->utilities->txt($key);
    }

    public function txtFill(string $key, string ...$values): string
    {
        return $this->utilities->txtFill($key, ...$values);
    }
}
