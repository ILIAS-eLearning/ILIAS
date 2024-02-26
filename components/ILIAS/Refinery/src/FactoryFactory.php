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

namespace ILIAS\Refinery;

use ILIAS\Language\Language;

/**
 * This Javaism is required to solve the problem that we do not have proper
 * \ILIAS\Language\Language during the setup. There might be setups where this would be
 * superfluous.
 */
class FactoryFactory
{
    public function build(\ILIAS\Data\Factory $dataFactory, \ILIAS\Language\Language $language): \ILIAS\Refinery\Factory
    {
        return new \ILIAS\Refinery\Factory($dataFactory, $language);
    }
}
