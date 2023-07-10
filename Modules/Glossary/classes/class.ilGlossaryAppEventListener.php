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
 ********************************************************************
 */

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilGlossaryAppEventListener implements ilAppEventListener
{
    /**
     * @inheritDoc
     */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        switch ($a_component) {
            case "Services/Object":
                switch ($a_event) {
                    case "beforeDeletion":
                        $handler = new ilGlossaryObjDeletionHandler();
                        $handler->processObjectDeletion($a_parameter["object"]->getId(), $a_parameter["object"]->getType());
                        break;
                }
                break;
            case "Modules/Glossary":
                switch ($a_event) {
                    case "deleteTerm":
                        $handler = new ilGlossaryObjDeletionHandler();
                        $handler->processTermDeletion($a_parameter["term_id"]);
                        break;
                }
        }
    }
}
