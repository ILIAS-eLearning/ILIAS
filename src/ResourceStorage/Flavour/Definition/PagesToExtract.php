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

namespace ILIAS\ResourceStorage\Flavour\Definition;

use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\ExtractPages;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class PagesToExtract implements FlavourDefinition
{
    public const FOREVER_ID = 'cbcb933538e2dfe9460d7a225f7b543b556ee580f41bd4f06cf16a4ca8dd8c8c';
    private const QUALITY = 75;
    protected bool $persist;
    protected int $max_size = 500;
    protected int $max_pages = 5;
    protected bool $fill = false;

    public function __construct(bool $persist, int $max_size = 500, int $max_pages = 5, bool $fill = false)
    {
        $this->persist = $persist;
        $this->max_size = $max_size;
        $this->max_pages = $max_pages;
        $this->fill = $fill;
    }

    public function getId(): string
    {
        return self::FOREVER_ID;
    }


    public function getFlavourMachineId(): string
    {
        return ExtractPages::ID;
    }

    public function getMaxPages(): int
    {
        return $this->max_pages;
    }


    public function getMaxSize(): int
    {
        return $this->max_size;
    }

    public function isFill(): bool
    {
        return $this->fill;
    }

    public function getQuality(): int
    {
        return self::QUALITY;
    }

    public function getInternalName(): string
    {
        return 'extracted_pages';
    }

    public function getVariantName(): ?string
    {
        return $this->max_size . 'x' . $this->max_size . ($this->fill ? '_fill' : '');
    }

    public function persist(): bool
    {
        return $this->persist;
    }
}
