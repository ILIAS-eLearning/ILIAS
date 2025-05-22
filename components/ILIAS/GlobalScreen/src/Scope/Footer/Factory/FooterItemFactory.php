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

namespace ILIAS\GlobalScreen\Scope\Footer\Factory;

use ILIAS\Data\URI;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class FooterItemFactory
{
    public function group(
        IdentificationInterface $identification,
        string $title
    ): Group {
        return new Group($identification, $title);
    }

    public function link(
        IdentificationInterface $identification,
        string $title
    ): Link {
        return new Link($identification, $title);
    }

    public function modal(
        IdentificationInterface $identification,
        string $title,
        \ILIAS\UI\Component\Modal\Modal $modal
    ): Modal {
        return new Modal($identification, $title, $modal);
    }

    public function permanent(
        IdentificationInterface $identification,
        string $title,
        URI $uri
    ): Permanent {
        return new Permanent($identification, $title, $uri);
    }

    public function text(
        IdentificationInterface $identification,
        string $text
    ): Text {
        return new Text($identification, $text);
    }

}
