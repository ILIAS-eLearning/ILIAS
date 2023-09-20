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

use PHPUnit\Framework\TestSuite;

/** @noRector */
require_once substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/vendor/composer/vendor/autoload.php';

class ilComponentsActiveRecordSuite extends TestSuite
{
    public static function suite(): self
    {
        $self = new self();
        /** @noRector */
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/ActiveRecord_/test/ilServicesActiveRecordConnectorTest.php");
        $self->addTestSuite("ilServicesActiveRecordConnectorTest");
        /** @noRector */
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/ActiveRecord_/test/ilServicesActiveRecordFieldTest.php");
        $self->addTestSuite("ilServicesActiveRecordFieldTest");

        return $self;
    }
}
