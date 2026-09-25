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

namespace ILIAS\Data\Privacy\Source\Known;

/**
 * Entry point to the catalogues of known ILIAS personal data columns.
 *
 * Obtain the instance via {@see \ILIAS\Data\Privacy\Services::sources()} —
 * do not instantiate this yourself. Further catalogues (mail, course,
 * forum, ...) are added here when the respective component is migrated.
 */
class KnownSources
{
    private ?UserSources $user = null;

    public function user(): UserSources
    {
        return $this->user ??= new UserSources();
    }
}
