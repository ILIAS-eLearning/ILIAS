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

namespace ILIAS\LearningSequence\Content;

/**
 * Contract for the mode specific content controllers (adaptive / sequential).
 *
 * The {@see \ilObjLearningSequenceContentGUI} acts as the main controller and
 * only decides which command has to be executed. The actual competence for a
 * command is delegated to the controller returned for the current LSO mode.
 */
interface LSOContentController
{
    /**
     * Renders the content management view (the default command).
     */
    public function manageContent(): void;

    /**
     * The list of commands this controller is responsible for. The main
     * controller uses it to decide whether a command has to be delegated here.
     *
     * @return string[]
     */
    public function getSupportedCommands(): array;
}
