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
use ILIAS\UI\Component\Legacy\Legacy;

class Renderer implements RendererInterface
{
    protected const FALLBACK_IMG = 'copyrights\all_rights_reserved.svg';

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
     * @return Icon[]|Link[]|Legacy[]
     */
    public function toUIComponents(CopyrightDataInterface $copyright): array
    {
        $res = [];
        $has_link = false;
        if (!is_null($image = $this->buildIcon($copyright))) {
            $res[] = $image;
        }
        if (!is_null($link = $this->buildLink($copyright))) {
            $res[] = $link;
            $has_link = true;
        }
        if ($copyright->fullName() && !$has_link) {
            $res[] = $this->textInLegacy($copyright->fullName());
        }
        return $res;
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

    protected function buildLink(CopyrightDataInterface $copyright): ?Link
    {
        if (!$copyright->link()) {
            return null;
        }
        return $this->standardLink(
            $copyright->fullName() !== '' ? $copyright->fullName() : (string) $copyright->link(),
            (string) $copyright->link()
        )->withAdditionalRelationshipToReferencedResource(Relationship::LICENSE);
    }

    protected function customIcon(string $src, string $alt): Icon
    {
        return $this->factory->symbol()->icon()->custom($src, $alt, Icon::MEDIUM);
    }

    protected function standardLink(string $label, string $action): Link
    {
        return $this->factory->link()->standard($label, $action);
    }

    protected function textInLegacy(string $text): Legacy
    {
        return $this->factory->legacy($text);
    }

    protected function getSourceFromIRSS(string $string_id): string
    {
        if ($identifier = $this->irss->manage()->find($string_id)) {
            return $this->irss->consume()->src($identifier)->getSrc();
        }
        return '';
    }
}
