<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Finder\Comparator;

use Exception;
use InvalidArgumentException;

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

/**
 * Class DateComparator
 * @package ILIAS\Filesystem\Finder\Comparator
 * @author  Michael Jansen <mjansen@databay.de>
 */
class DateComparator extends BaseComparator
{
    public function __construct(string $test)
    {
        if (!preg_match('#^\s*(==|!=|[<>]=?|after|since|before|until)?\s*(.+?)\s*$#i', $test, $matches)) {
            throw new InvalidArgumentException(sprintf('Don\'t understand "%s" as a date test.', $test));
        }

        try {
            $date = new \DateTime($matches[2]);
            $target = $date->format('U');
        } catch (Exception $e) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid date.', $matches[2]));
        }

        $operator = $matches[1] ?? '==';

        if ('since' === $operator || 'after' === $operator) {
            $operator = '>';
        }

        if ('until' === $operator || 'before' === $operator) {
            $operator = '<';
        }

        $this->setOperator($operator);
        $this->setTarget($target);
    }
}
