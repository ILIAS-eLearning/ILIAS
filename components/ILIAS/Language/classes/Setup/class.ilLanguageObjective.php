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

use ILIAS\Setup;

/**
 * Not every Objective in this component installs/updates languages (e.g.
 * ilDefaultLanguageSetObjective does not), so the "install languages via
 * ilSetupLanguage/InstallLanguage" dependencies and helper are not on this
 * common base - see ilLanguagesInstalledAndUpdatedObjective, the only
 * Objective that needs them.
 */
abstract class ilLanguageObjective implements Setup\Objective
{
    public function __construct()
    {
    }
}
