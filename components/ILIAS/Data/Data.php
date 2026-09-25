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

namespace ILIAS;

class Data implements Component\Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $provide[\ILIAS\Data\Factory::class] = static fn() =>
            new \ILIAS\Data\Factory();

        $define[] = \ILIAS\Data\Privacy\Services::class;

        $implement[\ILIAS\Data\Privacy\Services::class] = static fn() =>
            new \ILIAS\Data\Privacy\ServicesImpl(
                $internal[\ILIAS\Data\Privacy\Logger\CompositeLogger::class],
                $internal[\ILIAS\Data\Privacy\Source\Sources::class],
                $internal[\ILIAS\Data\Privacy\Purpose\Purposes::class],
            );

        $internal[\ILIAS\Data\Privacy\Logger\CompositeLogger::class] = static fn() =>
            new \ILIAS\Data\Privacy\Logger\CompositeLogger(
                $seek[\ILIAS\Data\Privacy\Logger\PrivacyLogger::class]
            );

        $internal[\ILIAS\Data\Privacy\Source\Sources::class] = static fn() =>
            new \ILIAS\Data\Privacy\Source\Sources();

        $internal[\ILIAS\Data\Privacy\Purpose\Purposes::class] = static fn() =>
            new \ILIAS\Data\Privacy\Purpose\Purposes();

        $provide[\ILIAS\Data\Privacy\Source\Sources::class] = static fn() =>
            $internal[\ILIAS\Data\Privacy\Source\Sources::class];

        $provide[\ILIAS\Data\Privacy\Purpose\Purposes::class] = static fn() =>
            $internal[\ILIAS\Data\Privacy\Purpose\Purposes::class];
    }
}
