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

namespace ILIAS\UI\Component\Modal\InterruptiveItem;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Image\Image;

/**
 * Interface Factory
 *
 * @package ILIAS\UI\Component\Modal
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     Standard Interruptive items represent objects that can generally be identified by a title.
     *   composition:
     *     A Standard Interruptive item is composed of an Id, title, description and an icon.
     * rules:
     *   usage:
     *     1: >
     *       A standard interruptive item MUST have a title.
     *     2: >
     *       A standard interruptive item SHOULD have an icon representing the affected object.
     *     3: >
     *       A standard interruptive item MAY have a description which helps to further identify the object.
     *       If an Interruptive modal displays multiple standard items having the the same title,
     *       the description MUST be used in order to distinguish these objects from each other.
     *     4: >
     *       If a standard interruptive item represents an ILIAS object, e.g. a course, then the Id, title, description
     *       and icon of the item MUST correspond to the Id, title, description and icon from the ILIAS object.
     * ---
     * @param string $id
     * @param string $title
     * @param Image $icon
     * @param string $description
     * @return \ILIAS\UI\Component\Modal\InterruptiveItem\Standard
     */
    public function standard(
        string $id,
        string $title,
        Image $icon = null,
        string $description = ''
    ): Standard;

    /**
     * ---
     * description:
     *   purpose:
     *     Key-Value Interruptive items represent objects that can be identified, either in
     *     general or in context, by a characteristic value, or for which a characteristic value
     *     paired to the object's name or title helps distinguish it from other objects.
     *   composition:
     *     A Key-Value Interruptive item is composed of an Id and a Key-Value pair.
     *   rivals:
     *     Standard Interruptive Item:
     *       Standard Interruptive items should be used over Key-Value items when the characteristic
     *       value is potentially verbose (e.g. a description or byline). For items representing
     *       ILIAS objects, standard items MUST be used.
     * rules:
     *   usage:
     *     1: >
     *       A key-value interruptive item MUST have a key and a value.
     *     2: >
     *       If an Interruptive modal displays multiple key-value items having the same key,
     *       the value MUST be used in order to distinguish these objects from each other.
     *   accessibility:
     *     1: >
     *        Key-Value pairs MUST be rendered as descriptive listings.
     * ---
     * @param string $id
     * @param string $key
     * @param string $value
     * @return \ILIAS\UI\Component\Modal\InterruptiveItem\KeyValue
     */
    public function keyValue(
        string $id,
        string $key,
        string $value
    ): KeyValue;
}
