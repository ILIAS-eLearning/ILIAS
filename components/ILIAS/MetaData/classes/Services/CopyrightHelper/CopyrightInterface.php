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

namespace ILIAS\MetaData\Services\CopyrightHelper;

use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Legacy\Content;

interface CopyrightInterface
{
    public function isDefault(): bool;

    public function isOutdated(): bool;

    public function identifier(): string;

    public function title(): string;

    public function description(): string;

    /**
     * The copyright as UI Components, as it should be presented in the
     * UI almost everywhere.
     * If only a string can be returned, it is returned in a legacy UI component.
     * @return Icon[]|Link[]|Content[]
     */
    public function presentAsUIComponents(): array;

    /**
     * The copyright just as its image, for use e.g. when the copyright
     * needs to be inserted into other KS components, and the specific
     * component it's presented as is important.
     * If the copyright does not have an image, null is returned.
     */
    public function presentAsImageOnly(): ?Icon;

    /**
     *  The copyright just as a link, for use e.g. when the copyright
     *  needs to be inserted into other KS components, and the specific
     *  component it's presented as is important.
     * If the copyright has no link, its full name is returned as a disabled link.
     * If it also does not have a full name, null is returned.
     */
    public function presentAsLinkOnly(): ?Link;

    /**
     * The copyright without image in a reduced presentation, for displaying
     * copyright where no UI components can be used (e.g. exports of tables).
     */
    public function presentAsString(): string;
}
