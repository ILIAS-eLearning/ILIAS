<?php declare(strict_types=1);

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
 * Base class for Web Link items
 * @author Tim Schmitz <schmitz@leifos.de>
 */
abstract class ilWebLinkBaseItem
{
    protected string $title;
    protected string $target;
    protected bool $active;

    /**
     * @var ilWebLinkBaseParameter[]
     */
    protected array $parameters;
    protected ?string $description;

    /**
     * @param string                    $title
     * @param string|null               $description
     * @param string                    $target
     * @param bool                      $active
     * @param ilWebLinkBaseParameter[]  $parameters
     */
    public function __construct(
        string $title,
        ?string $description,
        string $target,
        bool $active,
        array $parameters
    ) {
        $this->title = $title;
        $this->target = $target;
        $this->active = $active;
        $this->description = $description;
        $this->parameters = $parameters;
    }

    abstract public function isInternal() : bool;

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function getTarget() : string
    {
        return $this->target;
    }

    public function isActive() : bool
    {
        return $this->active;
    }

    /**
     * @return ilWebLinkBaseParameter[]
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }
}
