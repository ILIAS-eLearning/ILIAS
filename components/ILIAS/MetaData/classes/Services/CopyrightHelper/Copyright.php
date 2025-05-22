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

use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Legacy\Content;
use ILIAS\MetaData\Copyright\EntryInterface;
use ILIAS\MetaData\Copyright\RendererInterface;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as IdentifierHandler;

class Copyright implements CopyrightInterface
{
    protected RendererInterface $renderer;
    protected IdentifierHandler $identifier_handler;

    protected EntryInterface $entry;

    public function __construct(
        RendererInterface $renderer,
        IdentifierHandler $identifier_handler,
        EntryInterface $entry
    ) {
        $this->renderer = $renderer;
        $this->identifier_handler = $identifier_handler;

        $this->entry = $entry;
    }

    public function isDefault(): bool
    {
        return $this->entry->isDefault();
    }

    public function isOutdated(): bool
    {
        return $this->entry->isOutdated();
    }

    public function identifier(): string
    {
        return $this->identifier_handler->buildIdentifierFromEntryID($this->entry->id());
    }

    public function title(): string
    {
        return $this->entry->title();
    }

    public function description(): string
    {
        return $this->entry->description();
    }

    /**
     * The copyright as UI Components, as it should be presented in the
     * UI almost everywhere.
     * If only a string can be returned, it is returned in a legacy UI component.
     * @return Image[]|Link[]|Content[]
     */
    public function presentAsUIComponents(): array
    {
        return $this->renderer->toUIComponents($this->entry->copyrightData());
    }

    /**
     * The copyright without image in a reduced presentation, for displaying
     * copyright where no UI components can be used (e.g. exports of tables).
     */
    public function presentAsString(): string
    {
        return $this->renderer->toString($this->entry->copyrightData());
    }
}
