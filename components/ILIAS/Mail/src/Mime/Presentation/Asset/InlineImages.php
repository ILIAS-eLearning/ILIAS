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

namespace ILIAS\Mail\Mime\Presentation\Asset;

/**
 * @implements \IteratorAggregate<string, InlineImage>
 */
final readonly class InlineImages implements \IteratorAggregate, \Countable
{
    /**
     * @param array<string, InlineImage> $images
     */
    private function __construct(
        private array $images,
        private ?string $logo_cid
    ) {
    }

    public static function none(): self
    {
        return new self([], null);
    }

    public static function of(InlineImage ...$images): self
    {
        return self::none()->with(...$images);
    }

    public function with(InlineImage ...$images): self
    {
        $merged = $this->images;

        foreach ($images as $image) {
            $merged[$image->cid()] = $image;
        }

        return new self($merged, $this->logo_cid);
    }

    public function withLogo(InlineImage $logo): self
    {
        $merged = $this->images;
        $merged[$logo->cid()] = $logo;

        return new self($merged, $logo->cid());
    }

    public function logo(): ?InlineImage
    {
        return $this->logo_cid === null ? null : $this->images[$this->logo_cid];
    }

    public function hasLogo(): bool
    {
        return $this->logo_cid !== null;
    }

    public function first(): ?InlineImage
    {
        foreach ($this->images as $image) {
            return $image;
        }

        return null;
    }

    public function isEmpty(): bool
    {
        return $this->images === [];
    }

    public function count(): int
    {
        return \count($this->images);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->images);
    }
}
