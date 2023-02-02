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

namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent;

use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\Css;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\CssCollection;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\InlineCss;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\InlineCssCollection;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\Js;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\JsCollection;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\OnLoadCode;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\OnLoadCodeCollection;
use ILIAS\UI\Component\Layout\Page\Standard;
use ILIAS\Data\Meta\Html\OpenGraph;
use ILIAS\Data\Meta\Html;

/**
 * Class MetaContent
 *
 * @package ILIAS\GlobalScreen\Scope\LayoutDefinition\MetaContent
 */
class MetaContent
{
    public const MEDIA_SCREEN = "screen";

    private InlineCssCollection $inline_css;
    private OnLoadCodeCollection $on_load_code;
    private JsCollection $js;
    private CssCollection $css;
    private ?OpenGraph\TagCollection $og_meta_data;
    /**
     * @var Html\Tag[]
     */
    private array $meta_data;
    private string $base_url = "";
    private string $text_direction;
    protected string $resource_version;

    public function __construct(string $resource_version)
    {
        $this->resource_version = $resource_version;
        $this->css = new CssCollection($resource_version);
        $this->js = new JsCollection($resource_version);
        $this->on_load_code = new OnLoadCodeCollection($resource_version);
        $this->inline_css = new InlineCssCollection($resource_version);
        $this->og_meta_data = null;
        $this->meta_data = [];
    }

    /**
     * Reset
     */
    public function reset(): void
    {
        $this->css = new CssCollection($this->resource_version);
        $this->js = new JsCollection($this->resource_version);
        $this->on_load_code = new OnLoadCodeCollection($this->resource_version);
        $this->inline_css = new InlineCssCollection($this->resource_version);
        $this->og_meta_data = null;
        $this->meta_data = [];
    }

    public function addCss(string $path, string $media = self::MEDIA_SCREEN): void
    {
        $this->css->addItem(new Css($path, $this->resource_version, $media));
    }

    public function addJs(string $path, bool $add_version_number = false, int $batch = 2): void
    {
        $this->js->addItem(new Js($path, $this->resource_version, $add_version_number, $batch));
    }

    public function addInlineCss(string $content, string $media = self::MEDIA_SCREEN): void
    {
        $this->inline_css->addItem(new InlineCss($content, $this->resource_version, $media));
    }

    public function addOnloadCode(string $content, int $batch = 2): void
    {
        $this->on_load_code->addItem(new OnLoadCode($content, $this->resource_version, $batch));
    }

    public function addOpenGraphMetaDatum(OpenGraph\TagCollection $og_meta_data): void
    {
        $this->og_meta_data = $og_meta_data;
    }

    public function addMetaDatum(Html\Tag $meta_data): void
    {
        if ($meta_data instanceof OpenGraph\TagCollection || $meta_data instanceof OpenGraph\Tag) {
            throw new \LogicException(
                sprintf(
                    'Please use %s::addOpenGraphMetaDatum to add open-graph metadata.',
                    self::class
                )
            );
        }

        // keep user-defined keys unique, there should be no case where
        // multiple of the same keys are required.
        if ($meta_data instanceof Html\UserDefined) {
            $this->meta_data[$meta_data->getKey()] = $meta_data;
        } else {
            $this->meta_data[] = $meta_data;
        }
    }

    public function getInlineCss(): InlineCssCollection
    {
        return $this->inline_css;
    }

    public function getOnLoadCode(): OnLoadCodeCollection
    {
        return $this->on_load_code;
    }

    public function getJs(): JsCollection
    {
        return $this->js;
    }

    public function getCss(): CssCollection
    {
        return $this->css;
    }

    public function getOpenGraphMetaData(): ?OpenGraph\TagCollection
    {
        return $this->og_meta_data;
    }

    /**
     * @return Html\Tag[]
     */
    public function getMetaData(): array
    {
        return $this->meta_data;
    }

    public function setBaseURL(string $base_url): void
    {
        $this->base_url = $base_url;
    }

    public function getBaseURL(): string
    {
        return $this->base_url;
    }

    public function getTextDirection(): string
    {
        return $this->text_direction;
    }

    public function setTextDirection(string $text_direction): void
    {
        if (!in_array($text_direction, [Standard::LTR, Standard::RTL], true)) {
            throw new \InvalidArgumentException('$text_direction MUST be Standard::LTR, or Standard::RTL');
        }
        $this->text_direction = $text_direction;
    }
}
