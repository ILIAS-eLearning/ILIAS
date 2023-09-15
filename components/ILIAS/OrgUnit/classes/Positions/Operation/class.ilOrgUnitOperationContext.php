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
 ********************************************************************
 */

/**
 * Class ilOrgUnitOperationContext
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitOperationContext
{
    public const CONTEXT_OBJECT = "object";
    public const CONTEXT_CRS = "crs";
    public const CONTEXT_GRP = "grp";
    public const CONTEXT_IASS = "iass";
    public const CONTEXT_TST = "tst";
    public const CONTEXT_EXC = "exc";
    public const CONTEXT_SVY = "svy";
    public const CONTEXT_USRF = "usrf";
    public const CONTEXT_PRG = "prg";
    public const CONTEXT_ETAL = "etal";

    /**
     * @var array
     */
    public static array $available_contexts = [
        self::CONTEXT_OBJECT,
        self::CONTEXT_CRS,
        self::CONTEXT_GRP,
        self::CONTEXT_IASS,
        self::CONTEXT_TST,
        self::CONTEXT_EXC,
        self::CONTEXT_SVY,
        self::CONTEXT_USRF,
        self::CONTEXT_PRG,
        self::CONTEXT_ETAL,
    ];

    protected ?int $id = 0;
    protected string $context = self::CONTEXT_OBJECT;
    protected int $parent_context_id = 0;
    protected array $path_names = [self::CONTEXT_OBJECT];
    protected array $path_ids = [0];

    public function __construct(?int $id = 0)
    {
        $this->id = $id;
        $this->path_ids = [$id];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function withContext(string $context): self
    {
        $clone = clone $this;
        $clone->context = $context;
        return $clone;
    }

    public function getParentContextId(): int
    {
        return $this->parent_context_id;
    }

    public function withParentContextId(int $parent_context_id): self
    {
        $clone = clone $this;
        $clone->parent_context_id = $parent_context_id;
        return $clone;
    }

    public function getPathNames(): array
    {
        return $this->path_names;
    }

    public function withPathNames(array $path_names): self
    {
        $clone = clone $this;
        $clone->path_names = $path_names;
        return $clone;
    }

    public function getPathIds(): array
    {
        return $this->path_ids;
    }

    public function withPathIds(array $path_ids): self
    {
        $clone = clone $this;
        $clone->path_ids = $path_ids;
        return $clone;
    }

    /**
     * @deprecated Please use getPathNames()
     * @return string[]
     */
    public function getPopulatedContextNames(): array
    {
        return $this->getPathNames();
    }



    /**
     * @deprecated Please use getPathIds()
     * @return int[]
     */
    public function getPopulatedContextIds(): array
    {
        return $this->getPathIds();
    }
}
