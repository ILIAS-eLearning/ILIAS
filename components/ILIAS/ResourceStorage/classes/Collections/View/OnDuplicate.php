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

namespace ILIAS\components\ResourceStorage\Collections\View;

/**
 * Controls how a collection reacts when a file is uploaded whose name matches
 * an already existing resource in the same collection.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
enum OnDuplicate: int
{
    /**
     * Allow duplicates: the file is always stored as a new, separate resource,
     * even if another resource with the same name already exists.
     */
    case ALLOW = 1;

    /**
     * On duplicate, overwrite the existing resource with a new revision and
     * delete all previous revisions (no history is kept).
     */
    case REPLACE = 2;

    /**
     * On duplicate, overwrite the existing resource by appending a new revision
     * while keeping the previous revisions as history.
     */
    case APPEND_REVISION = 3;

    /**
     * On duplicate, reject the uploaded file: the existing resource is left
     * untouched and the new file is not stored.
     */
    case REJECT = 4;
}
