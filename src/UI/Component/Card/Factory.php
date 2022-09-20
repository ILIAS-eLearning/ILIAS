<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Component\Card;

use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Button\Shy;

/**
 * This is how the factory for UI elements looks.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *       The Standard Card is the default Card to be used in ILIAS. If
     *       there is no good reason using another Card instance in ILIAS, this
     *       is the one that should be used.
     *
     * featurewiki:
     *       - http://www.ilias.de/docu/goto_docu_wiki_wpage_3208_1357.html
     *
     * rules:
     *   usage:
     *       1: >
     *          Standard Card MUST be used if there is no good reason using
     *          another instance.
     * ---
     * @param string|Shy $title
     * @param \ILIAS\UI\Component\Image\Image $image
     * @return \ILIAS\UI\Component\Card\Standard
     */
    public function standard($title, Image $image = null): Standard;

    /**
     * ---
     * description:
     *   purpose: >
     *      Repository Object cards are used in contexts that more visual information about the repository object
     *      type is needed.
     *   composition: >
     *      Repository Object cards add icons on a darkened layer over the image. This Darkened layer is divided into
     *      4 horizontal cells where the icons can be located.
     *      Starting from the left, the icons have the following order:
     *          Cell 1: Object type (UI Icon)
     *          Cell 2: Learning Progress (UI ProgressMeter in the mini version) or Certificate (UI Icon)
     *          Cell 3: Empty
     *          Cell 4: Actions (UI Dropdown)
     *      Cells and its content are responsively adapted if the size of the screen is changed.
     *   rivals:
     *      Item: Items are used in lists or similar contexts.
     * rules:
     *   usage:
     *       1: Repository Object Cards MAY contain a UI Icon displaying the object type.
     *       2: Repository Object Cards MAY contain a UI ProgressMeter displaying the learning progress of the user.
     *       3: Repository Object Cards MAY contain a UI Icon displaying a certificate icon if the user finished the task.
     *       4: Repository Object Cards MAY contain a UI ProgressMeter OR UI Icon certificate, NOT both.
     * featurewiki:
     *       - https://docu.ilias.de/goto_docu_wiki_wpage_4921_1357.html
     *
     * ---
     * @param string|Shy $title
     * @param \ILIAS\UI\Component\Image\Image $image
     * @return \ILIAS\UI\Component\Card\RepositoryObject
     */
    public function repositoryObject($title, Image $image): RepositoryObject;
}
