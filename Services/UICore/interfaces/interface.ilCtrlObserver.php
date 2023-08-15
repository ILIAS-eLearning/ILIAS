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

/**
 * @author       Fabian Schmid <fabian@sr.solutions>
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
interface ilCtrlObserver
{
    /**
     * Unique identifier of the implementing event-listener.
     */
    public function getId(): string;

    /**
     * Recieves an ilCtrl event and handles it appropriately.
     *
     * @param string|null $data ilCtrlEvent::COMMAND_CLASS_FORWARD: the class name in SnakeCase
     *                          ilCtrlEvent::COMMAND_DETERMINATION: the determined command or null
     */
    public function update(ilCtrlEvent $event, ?string $data): void;
}
