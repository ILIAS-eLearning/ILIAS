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

namespace ILIAS\Data\Privacy;

/**
 * A group of personal data that belongs together but comes from different
 * places, e.g. the profile of a user. Unlike a compound type such as
 * {@see Types\PostalAddress}, a bundle has no value of its own and is
 * never resolved: its parts are.
 *
 * Bundles nest, up to all personal data about one person.
 */
interface PrivacyDataBundle
{
    /**
     * The parts, e.g. the typed fields of a profile.
     *
     * @return iterable<string, PrivacyDataType<mixed>|PrivacyDataBundle> key => part
     */
    public function parts(): iterable;
}
