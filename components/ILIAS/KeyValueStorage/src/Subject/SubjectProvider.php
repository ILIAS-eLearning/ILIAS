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

namespace ILIAS\KeyValueStorage\Subject;

/**
 * A component that names subjects, contributed via
 * `$contribute[\ILIAS\KeyValueStorage\Subject\SubjectProvider::class]`.
 *
 * The name is the origin of every subject the provider names and is stored with
 * each of its values, so two providers can never share rows, and purging a
 * subject of one provider never reaches the data of another.
 *
 * The build creates every provider without arguments to reject two providers
 * with the same name, so a provider must not require constructor arguments, and
 * its name must not depend on them.
 *
 * Checking the provider of a subject guards against misconfiguration and name
 * collisions, not against access: any component can create a provider.
 */
interface SubjectProvider
{
    public const int MAX_NAME_LENGTH = 64;

    /**
     * Unique among all providers, a lowercase identifier, e.g. "authentication".
     */
    public function name(): string;
}
