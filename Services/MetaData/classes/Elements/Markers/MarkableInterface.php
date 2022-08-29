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

namespace ILIAS\MetaData\Elements\Markers;

use ILIAS\MetaData\Elements\Data\DataInterface;

interface MarkableInterface
{
    /**
     * Elements can be marked to be created, updated or deleted.
     */
    public function isMarked(): bool;

    public function getMarker(): ?MarkerInterface;

    /**
     * Leaves a trail of markers from this element up to the root element,
     * or up to the first already marked element.
     * Places a marker with the given action and data value on this element,
     * and neutral markers on the others, except for scaffolds: when the
     * inital element is marked to be created or updated, so are the scaffolds
     * on the way to root.
     */
    public function mark(
        MarkerFactoryInterface $factory,
        Action $action,
        string $data_value = ''
    );
}
