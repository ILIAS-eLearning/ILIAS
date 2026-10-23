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

namespace ILIAS\Language\ComponentTranslation;

/**
 * Optional addition to LanguageFileDirectory for shipped `.po` files not named `<prefix>_<lang>.po`
 * (separate, so implementations and test doubles need not change; MigratedLanguageFilePaths::
 * shippedFileNamePattern() falls back to `<prefix>_%s`).
 *
 * The name must not depend on the module name where that is expected to change (e.g. a plugin becoming
 * a component): `ilias_%s` stays stable.
 */
interface NamesShippedLanguageFiles
{
    /**
     * The name without ".po": exactly one "%s" (language key), otherwise only letters, digits, "_", "-"
     * and "." (no "/", no ".."), e.g. "ilias_%s".
     */
    public function getShippedFileNamePattern(): string;
}
