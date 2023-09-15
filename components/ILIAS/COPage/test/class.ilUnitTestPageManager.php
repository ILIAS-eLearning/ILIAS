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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUnitTestPageManager implements \ILIAS\COPage\Page\PageManagerInterface
{
    protected ilPageObject $test_get;

    public function __construct()
    {
    }

    public function mockGet(
        ilPageObject $page_object
    ) {
        $this->test_get = $page_object;
    }

    public function get(
        string $parent_type,
        int $id = 0,
        int $old_nr = 0,
        string $lang = "-"
    ): ilPageObject {
        return $this->test_get;
    }
}
