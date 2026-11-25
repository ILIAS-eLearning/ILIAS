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

namespace ILIAS\StaticURL\Request;

/**
 * Describes why a static URL is requested. Handlers MAY use it to choose
 * a different target, handlers which do not know an intent ignore it.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
enum Intent: string
{
    public const PARAMETER = 'intent';

    /**
     * The handler decides which target fits best (e.g. a download for files).
     */
    case DEFAULT = 'default';
    /**
     * The object shall only be shown, no action such as a download or
     * starting a player shall be triggered.
     */
    case SHOW = 'show';
}
