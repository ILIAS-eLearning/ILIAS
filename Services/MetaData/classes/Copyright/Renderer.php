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
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Legacy\Legacy;

class Renderer implements RendererInterface
{
    protected Factory $factory;

    public function __construct(
        Factory $factory
    ) {
        $this->factory = $factory;
    }

    /**
     * @return Image[]|Link[]|Legacy[]
     */
    public function toUIComponents(CopyrightDataInterface $copyright): array
    {
        $res = [];
        $has_link = false;
        if (!is_null($image = $this->buildImage($copyright))) {
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

    protected function buildImage(CopyrightDataInterface $copyright): ?Image
    {
        if (!$copyright->imageLink()) {
            return null;
        }
        return $this->getImage(
            (string) $copyright->imageLink(),
            $copyright->altText(),
            (string) $copyright->link()
        );
    }

    protected function getImage(string $src, string $alt, string $link): Image
    {
        $image = $this->standardImage($src, $alt);
        if ($link !== '') {
            $image = $image->withAction($link);
        }
        return $image;
    }

    protected function buildLink(CopyrightDataInterface $copyright): ?Link
    {
        if (!$copyright->link()) {
            return null;
        }
        return $this->standardLink(
            $copyright->fullName() !== '' ? $copyright->fullName() : (string) $copyright->link(),
            (string) $copyright->link()
        );
    }

    protected function standardImage(string $src, string $alt): Image
    {
        return $this->factory->image()->standard($src, $alt);
    }

    protected function standardLink(string $label, string $action): Link
    {
        return $this->factory->link()->standard($label, $action);
    }

    protected function textInLegacy(string $text): Legacy
    {
        return $this->factory->legacy($text);
    }
}
