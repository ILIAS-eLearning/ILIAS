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
namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information;

use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\BaseTypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\BaseTypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\TypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;

/**
 * Class TypeInformation
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
final class TypeInformation
{
    /**
     * @var \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\TypeRenderer
     */
    private $renderer;
    /**
     * @var \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem
     */
    private $instance;
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $type_name_for_presentation;
    /**
     * @var string
     */
    private $type_byline_for_presentation;
    /**
     * @var \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler
     */
    private $type_handler;
    /**
     * @var bool
     */
    private $creation_prevented = false;

    public function __construct(
        string $type,
        string $type_name_for_presentation,
        TypeRenderer $renderer = null,
        TypeHandler $type_handler = null,
        string $type_byline = null
    ) {
        $this->instance = new $type(new NullIdentification());
        $this->type = $type;
        $this->type_name_for_presentation = $type_name_for_presentation;
        $this->type_handler = $type_handler ?: new BaseTypeHandler();
        $this->renderer = $renderer ?: new BaseTypeRenderer();
        $this->type_byline_for_presentation = $type_byline ?: "";
    }

    /**
     * @return bool
     */
    public function isCreationPrevented() : bool
    {
        return $this->creation_prevented;
    }

    /**
     * @param bool $creation_prevented
     */
    public function setCreationPrevented(bool $creation_prevented) : void
    {
        $this->creation_prevented = $creation_prevented;
    }

    /**
     * @return bool
     */
    public function isParent() : bool
    {
        if ($this->instance instanceof Lost) {
            return false;
        }

        return $this->instance instanceof isParent;
    }

    /**
     * @return bool
     */
    public function isTop() : bool
    {
        if ($this->instance instanceof Lost) {
            return false;
        }

        return $this->instance instanceof isTopItem;
    }

    /**
     * @return bool
     */
    public function isChild() : bool
    {
        if ($this->instance instanceof Lost) {
            return false;
        }

        return $this->instance instanceof isChild;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type) : void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTypeNameForPresentation() : string
    {
        return $this->type_name_for_presentation;
    }

    /**
     * @param string $type_name_for_presentation
     */
    public function setTypeNameForPresentation(string $type_name_for_presentation) : void
    {
        $this->type_name_for_presentation = $type_name_for_presentation;
    }

    /**
     * @return string
     */
    public function getTypeBylineForPresentation() : string
    {
        return $this->type_byline_for_presentation;
    }

    /**
     * @param string $type_byline_for_presentation
     */
    public function setTypeBylineForPresentation(string $type_byline_for_presentation) : void
    {
        $this->type_byline_for_presentation = $type_byline_for_presentation;
    }

    /**
     * @return TypeHandler
     */
    public function getTypeHandler() : TypeHandler
    {
        return $this->type_handler;
    }

    /**
     * @param TypeHandler $type_handler
     */
    public function setTypeHandler(TypeHandler $type_handler) : void
    {
        $this->type_handler = $type_handler;
    }

    /**
     * @return TypeRenderer
     */
    public function getRenderer() : TypeRenderer
    {
        return $this->renderer;
    }

    /**
     * @param TypeRenderer $renderer
     */
    public function setRenderer(TypeRenderer $renderer) : void
    {
        $this->renderer = $renderer;
    }
}
