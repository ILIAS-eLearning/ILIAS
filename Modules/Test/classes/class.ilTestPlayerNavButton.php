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
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestPlayerNavButton extends ilLinkButton
{
    /**
     * @var string
     */
    private $nextCommand = '';

    // fau: testNav - add glyphicon support for navigation buttons
    private $leftGlyph = '';
    private $rightGlyph = '';

    public function setLeftGlyph($glyph)
    {
        $this->leftGlyph = $glyph;
    }

    public function setRightGlyph($glyph)
    {
        $this->rightGlyph = $glyph;
    }

    protected function renderCaption(): string
    {
        $caption = '';

        if ($this->leftGlyph) {
            $caption .= '<span class="' . $this->leftGlyph . '"></span> ';
        }

        $caption .= parent::renderCaption();

        if ($this->rightGlyph) {
            $caption .= ' <span class="' . $this->rightGlyph . '"></span>';
        }

        return $caption;
    }
    // fau.

    /**
     * @return string
     */
    public function getNextCommand(): string
    {
        return $this->nextCommand;
    }

    /**
     * @param string $nextCommand
     */
    public function setNextCommand($nextCommand)
    {
        $this->nextCommand = $nextCommand;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $this->prepareRender();

        $attr = array(
            'href' => $this->getUrl() ? $this->getUrl() : "#",
            'target' => $this->getTarget()
        );

        if (strlen($this->getNextCommand())) {
            $attr['data-nextcmd'] = $this->getNextCommand();
        }

        return '<a' . $this->renderAttributes($attr) . '>' . $this->renderCaption() . '</a>';
    }

    public static function getInstance(): self
    {
        return new self(self::TYPE_LINK);
    }
}
