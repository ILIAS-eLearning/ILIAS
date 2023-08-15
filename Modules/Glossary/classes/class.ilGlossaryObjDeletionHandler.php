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

use ILIAS\Glossary;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilGlossaryObjDeletionHandler
{
    protected Glossary\InternalDomainService $domain_service;

    public function __construct()
    {
        global $DIC;

        $this->domain_service = $DIC->glossary()->internal()->domain();
    }

    public function processObjectDeletion(int $obj_id, string $obj_type): void
    {
        if ($obj_type == "usr" && ilObject::_lookupType($obj_id) == "usr") {
            $flashcard_manager = $this->domain_service->flashcard(0, $obj_id);
            $flashcard_manager->deleteAllUserEntries();
        }
        if ($obj_type == "glo" && ilObject::_lookupType($obj_id) == "glo") {
            $flashcard_manager = $this->domain_service->flashcard((int) current(\ilObject::_getAllReferences($obj_id)));
            $flashcard_manager->deleteAllGlossaryEntries();
        }
    }

    public function processTermDeletion(int $term_id): void
    {
        $flashcard_manager = $this->domain_service->flashcard();
        $flashcard_manager->deleteAllTermEntries($term_id);
    }
}
