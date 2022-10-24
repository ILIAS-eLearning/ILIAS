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

namespace ILIAS\UI\Implementation\Component\Card;

use ILIAS\UI\Component\Card as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Clickable;

class Card implements C\Card
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    /**
     * @var \ILIAS\UI\Implementation\Component\Button\Shy|string
     */
    protected $title;
    protected Component $header_section;

    /**
     * @var Component[]|[]
     */
    protected array $content_sections = [];
    protected ?Image $image;

    /**
     * @var string|Signal[]
     */
    protected $title_action = '';
    protected bool $highlight = false;

    /**
     * @param string|Shy$title
     * @param Image|null $image
     */
    public function __construct($title, Image $image = null)
    {
        if (!$title instanceof Shy) {
            $this->checkStringArg("title", $title);
        }

        $this->title = $title;
        $this->image = $image;
    }

    /**
     * @inheritdoc
     */
    public function withTitle($title): C\Card
    {
        if (!$title instanceof Shy) {
            $this->checkStringArg("title", $title);
        }

        $clone = clone $this;
        $clone->title = $title;

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function withImage(Image $image): C\Card
    {
        $clone = clone $this;
        $clone->image = $image;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getImage(): ?Image
    {
        return $this->image;
    }

    /**
     * @inheritdoc
     */
    public function withSections(array $sections): C\Card
    {
        $classes = [Component::class];
        $this->checkArgListElements("sections", $sections, $classes);

        $clone = clone $this;
        $clone->content_sections = $sections;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getSections(): array
    {
        return $this->content_sections;
    }

    /**
     * @inheritdoc
     */
    public function withTitleAction($action): C\Card
    {
        $this->checkStringOrSignalArg("title_action", $action);

        $clone = clone $this;
        if (is_string($action)) {
            $clone->title_action = $action;
        } else {
            /**
             * @var $action Signal
             */
            $clone->title_action = null;
            $clone->setTriggeredSignal($action, "click");
        }

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getTitleAction()
    {
        if ($this->title_action !== null) {
            return $this->title_action;
        }
        return $this->getTriggeredSignalsFor("click");
    }

    /**
     * @inheritdoc
     */
    public function withHighlight(bool $status): Card
    {
        $clone = clone $this;
        $clone->highlight = $status;

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isHighlighted(): bool
    {
        return $this->highlight;
    }

    /**
     * @inheritdoc
     */
    public function withOnClick(Signal $signal): Clickable
    {
        return $this->withTriggeredSignal($signal, 'click');
    }
    /**
     * @inheritdoc
     */
    public function appendOnClick(Signal $signal): Clickable
    {
        return $this->appendTriggeredSignal($signal, 'click');
    }
}
