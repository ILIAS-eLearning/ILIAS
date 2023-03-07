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

namespace ILIAS\Filesystem\Finder\Comparator;

use InvalidArgumentException;

/**
 * Class NumberComparator
 * @package ILIAS\Filesystem\Finder\Comparator
 * @author  Michael Jansen <mjansen@databay.de>
 */
class NumberComparator extends BaseComparator
{
    public function __construct(string $test)
    {
        if (!preg_match('#^\s*(==|!=|[<>]=?)?\s*([0-9\.]+)\s*([kmg]i?)?\s*$#i', $test, $matches)) {
            throw new InvalidArgumentException(sprintf('Don\'t understand "%s" as a number test.', $test));
        }

        $target = $matches[2];
        if (!is_numeric($target)) {
            throw new InvalidArgumentException(sprintf('Invalid number "%s".', $target));
        }

        if (isset($matches[3])) {
            switch (strtolower($matches[3])) {
                case 'k':
                    $target *= 1000;
                    break;

                case 'ki':
                    $target *= 1024;
                    break;

                case 'm':
                    $target *= 1_000_000;
                    break;

                case 'mi':
                    $target *= 1024 * 1024;
                    break;

                case 'g':
                    $target *= 1_000_000_000;
                    break;

                case 'gi':
                    $target *= 1024 * 1024 * 1024;
                    break;
            }
        }

        $this->setTarget((string) $target);
        $this->setOperator($matches[1] ?? '==');
    }
}
