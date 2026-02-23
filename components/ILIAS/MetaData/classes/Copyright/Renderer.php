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

namespace ILIAS\MetaData\Copyright;

use ILIAS\UI\Factory;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Link\Link;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\UI\Component\Link\Relationship;
use ILIAS\UI\Component\Legacy\Content;

class Renderer implements RendererInterface
{
    protected const string FALLBACK_IMG = 'copyrights\all_rights_reserved.svg';

    protected Factory $factory;
    protected IRSS $irss;

    public function __construct(
        Factory $factory,
        IRSS $irss
    ) {
        $this->factory = $factory;
        $this->irss = $irss;
    }

    /**
     * @return Icon[]|Link[]|Content[]
     */
    public function toUIComponents(CopyrightDataInterface $copyright): array
    {
        $res = [];
        $has_link = false;
        if (!is_null($image = $this->buildIcon($copyright))) {
            $res[] = $image;
        }
        if (!is_null($link = $this->buildLink($copyright, false))) {
            $res[] = $link;
            $has_link = true;
        }
        if ($copyright->fullName() && !$has_link) {
            $res[] = $this->textInLegacy($copyright->fullName());
        }
        return $res;
    }

    public function toImageOnly(CopyrightDataInterface $copyright): ?Icon
    {
        return $this->buildIcon($copyright);
    }

    public function toLinkOnly(CopyrightDataInterface $copyright): ?Link
    {
        return $this->buildLink($copyright, true);
    }

    public function toString(CopyrightDataInterface $copyright): string
    {
        $full_name = $copyright->fullName();
        $link = $copyright->link();

        $res = [];
        if ($full_name !== '') {
            $res[] = $full_name;
        }
        if ($link !== null) {
            $res[] = (string) $link;
        }

        return implode(' ', $res);
    }

    protected function buildIcon(CopyrightDataInterface $copyright): ?Icon
    {
        if (!$copyright->hasImage()) {
            if ($copyright->fallBackToDefaultImage()) {
                return $this->buildFallBackIcon($copyright);
            }
            return null;
        }
        if ($copyright->isImageLink()) {
            return $this->buildIconFromLink($copyright);
        } else {
            return $this->buildIconFromFile($copyright);
        }
    }

    protected function buildIconFromLink(CopyrightDataInterface $copyright): Icon
    {
        return $this->customIcon(
            (string) $copyright->imageLink(),
            $copyright->altText()
        );
    }

    protected function buildIconFromFile(CopyrightDataInterface $copyright): ?Icon
    {
        if ($from_irss = $this->getSourceFromIRSS($copyright->imageFile())) {
            $src = $from_irss;
        } else {
            return null;
        }

        return $this->customIcon(
            $src,
            $copyright->altText()
        );
    }

    protected function buildFallBackIcon(CopyrightDataInterface $copyright): ?Icon
    {
        return $this->customIcon(
            $this->getFallBackSrc(),
            $copyright->altText()
        );
    }

    protected function getFallBackSrc(): string
    {
        return \ilUtil::getImagePath(self::FALLBACK_IMG);
    }

    protected function buildLink(CopyrightDataInterface $copyright, bool $allow_empty_link): ?Link
    {
        if (
            !$copyright->link() &&
            (!$allow_empty_link || $copyright->fullName() === '')
        ) {
            return null;
        }
        return $this->standardLink(
            $copyright->fullName() !== '' ? $copyright->fullName() : (string) $copyright->link(),
            (string) $copyright->link(),
            $copyright->link() !== null ? Relationship::LICENSE : null,
            $copyright->link() === null
        );
    }

    protected function customIcon(string $src, string $alt): Icon
    {
        return $this->factory->symbol()->icon()->custom($src, $alt, Icon::MEDIUM);
    }

    protected function standardLink(
        string $label,
        string $action,
        ?Relationship $relationship,
        bool $disabled
    ): Link {
        $link = $this->factory->link()->standard($label, $action);
        if ($relationship !== null) {
            $link = $link->withAdditionalRelationshipToReferencedResource($relationship);
        }
        if ($disabled) {
            $link = $link->withDisabled();
        }
        return $link;
    }

    protected function textInLegacy(string $text): Content
    {
        return $this->factory->legacy()->content($text);
    }

    protected function getSourceFromIRSS(string $string_id): string
    {
        if ($identifier = $this->irss->manage()->find($string_id)) {
            return $this->irss->consume()->src($identifier)->getSrc();
        }
        return '';
    }
}
