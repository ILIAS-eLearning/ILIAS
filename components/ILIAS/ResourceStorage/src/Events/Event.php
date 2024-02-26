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
 */

declare(strict_types=1);

namespace ILIAS\ResourceStorage\Events;

/**
 * @author       Fabian Schmid <fabian@sr.solutions>
 */
enum Event: string
{
    /**
     * event string being used if a new Resource has been stored to the IRSS.
     */
    //    case RESOURCE_CREATED = 'resource:created'; // will follow later
    /**
     * event string being used if a Resource has been deleted from the IRSS.
     */
    //    case RESOURCE_DELETED = 'resource:deleted'; // will follow later

    /**
     * event string being used if a new Resource has been assigned and stored to a collection.
     */
    case COLLECTION_RESOURCE_ADDED = 'collection:resource:added';
    /**
     * event string being used if a Resource has been deassigned from collection.
     */
    // case COLLECTION_RESOURCE_REMOVED = 'collection:resource:removed'; // will follow later

    /**
     * event string for all possible events.
     */
    case ALL = '*';
}
