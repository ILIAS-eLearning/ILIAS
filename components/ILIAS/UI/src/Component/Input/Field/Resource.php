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

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Symbol\Glyph\Glyph;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Resource
{
    /**
     * Returns the unique identifier of this resource. Since we are working with
     * abstract resources, this value will not always be an integer. The IRSS e.g.
     * works with string identifiers.
     */
    public function getId(): string;

    /**
     * Returns the display value of this resource.
     */
    public function getTitle(): string;

    /**
     * Returns a Glyph component visually representing this resource. Defaults to
     * an abbreviation featuring the first letter of the resource title.
     */
    public function getIcon(): ?Glyph;
}
