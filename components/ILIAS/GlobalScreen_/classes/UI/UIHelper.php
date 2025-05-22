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

namespace ILIAS\GlobalScreen_\UI;

use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Factory;
use ILIAS\UI\Component\Image\Image;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait UIHelper
{
    protected function nok(Factory $f, bool $as_image = false): Icon|Image
    {
        if ($as_image) {
            return $f->image()->standard(
                'assets/images/standard/icon_unchecked.svg',
                ''
            );
        }

        return $f->symbol()->icon()->custom(
            'assets/images/standard/icon_unchecked.svg',
            '',
            'small'
        );
    }

    protected function ok(Factory $f, bool $as_image = false): Icon|Image
    {
        if ($as_image) {
            return $f->image()->standard(
                'assets/images/standard/icon_checked.svg',
                ''
            );
        }

        return $f->symbol()->icon()->custom(
            'assets/images/standard/icon_checked.svg',
            '',
            'small'
        );
    }
}
