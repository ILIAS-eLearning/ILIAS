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

/**
 * Image Link Button GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @deprecated use KS Buttons instead
 */
class ilImageLinkButton extends ilLinkButton
{
    protected string $src = "";
    protected bool $force_title = false;

    public static function getInstance(): self
    {
        return new self(self::TYPE_LINK);
    }


    //
    // properties
    //

    public function setImage(string $a_value, bool $a_is_internal = true): void
    {
        if ($a_is_internal) {
            $a_value = ilUtil::getImagePath($a_value);
        }
        $this->src = trim($a_value);
    }

    public function getImage(): string
    {
        return $this->src;
    }

    public function forceTitle(bool $a_value): void
    {
        $this->force_title = $a_value;
    }

    public function hasForceTitle(): bool
    {
        return $this->force_title;
    }


    //
    // render
    //

    protected function prepareRender(): void
    {
        // get rid of parent "submit" css class...
    }

    protected function renderCaption(): string
    {
        $attr = array();
        $attr["src"] = $this->getImage();
        $attr["alt"] = $this->getCaption();
        if ($this->hasForceTitle()) {
            $attr["title"] = $this->getCaption();
        }
        return '<img' . $this->renderAttributesHelper($attr) . ' />';
    }
}
