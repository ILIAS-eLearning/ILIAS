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

/**
 * Immutable class for Web Link items
 * @author Tim Schmitz <schmitz@leifos.de>
 */
abstract class ilWebLinkItem extends ilWebLinkBaseItem
{
    protected int $webr_id;
    protected int $link_id;

    protected DateTimeImmutable $create_date;
    protected DateTimeImmutable $last_update;

    /**
     * @var ilWebLinkParameter[]
     */
    protected array $parameters;

    /**
     * @param int                   $webr_id
     * @param int                   $link_id
     * @param string                $title
     * @param string|null           $description
     * @param string                $target
     * @param bool                  $active
     * @param DateTimeImmutable     $create_date
     * @param DateTimeImmutable     $last_update
     * @param ilWebLinkParameter[]  $parameters
     */
    public function __construct(
        int $webr_id,
        int $link_id,
        string $title,
        ?string $description,
        string $target,
        bool $active,
        DateTimeImmutable $create_date,
        DateTimeImmutable $last_update,
        array $parameters
    ) {
        parent::__construct($title, $description, $target, $active, $parameters);
        $this->webr_id = $webr_id;
        $this->link_id = $link_id;
        $this->create_date = $create_date;
        $this->last_update = $last_update;
    }

    abstract public function getResolvedLink(bool $with_parameters = true): string;

    abstract public function isInternal(): bool;

    public function toXML(ilXmlWriter $writer, int $position): void
    {
        $writer->xmlStartTag(
            'WebLink',
            [
                'id' => $this->getLinkId(),
                'active' => (int) $this->isActive(),
                'position' => $position,
                'internal' => (int) $this->isInternal(),
            ]
        );
        $writer->xmlElement('Title', [], $this->getTitle());
        $writer->xmlElement('Description', [], $this->getDescription());
        $writer->xmlElement('Target', [], $this->getTarget());

        foreach ($this->getParameters() as $parameter) {
            $parameter->toXML($writer);
        }

        $writer->xmlEndTag('WebLink');
    }

    public function getWebrId(): int
    {
        return $this->webr_id;
    }

    public function getLinkId(): int
    {
        return $this->link_id;
    }

    public function getCreateDate(): DateTimeImmutable
    {
        return $this->create_date;
    }

    public function getLastUpdate(): DateTimeImmutable
    {
        return $this->last_update;
    }

    /**
     * @return ilWebLinkParameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
