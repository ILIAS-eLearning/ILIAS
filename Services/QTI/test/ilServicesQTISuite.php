<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

require_once __DIR__ . '/bootstrap.php';

/**
 * @author Lukas Scharmer <lscharmer@databay.de>
 */
class ilServicesQTISuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new self();

        $dir = __DIR__;
        $a = [];
        foreach (array_filter(explode("\n", `find "$dir" -name \*.php -and -not -name ilServicesQTISuite.php -print`)) as $file) {
            $className = `echo "$(basename "$file")" | sed s/^class.// | cut -d . -f 1 | tr -d '\n'`;
            require_once $file;
            $a[] = $className;
        }
        array_map([$suite, 'addTestSuite'], array_filter($a, 'class_exists'));
        // $suite->addTestSuite(ilBuddySystemTestSuite::class);

        return $suite;
    }
}
