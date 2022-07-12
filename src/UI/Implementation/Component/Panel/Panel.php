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
 
namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Component;

/**
 * Class Panel
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Panel implements C\Panel\Panel
{
    use ComponentHelper;

    /**
     * @var Component[]|Component
     */
    private $content;
    protected string $title;
    protected ?C\Dropdown\Standard $actions = null;

    /**
     * @param Component[]|Component $content
     */
    public function __construct(string $title, $content)
    {
        $content = $this->toArray($content);
        $types = [Component::class];
        $this->checkArgListElements("content", $content, $types);

        $this->title = $title;
        $this->content = $content;
    }

    /**
     * @inheritdoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function withActions(C\Dropdown\Standard $actions) : C\Panel\Panel
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActions() : ?C\Dropdown\Standard
    {
        return $this->actions;
    }
}
