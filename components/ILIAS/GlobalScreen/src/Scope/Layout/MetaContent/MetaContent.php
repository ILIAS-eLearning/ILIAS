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

use ILIAS\Data\Meta\Html\OpenGraph\TagCollection;
use ILIAS\Data\Meta\Html\Tag;
use ILIAS\Data\Meta\Html\UserDefined;
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
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class MetaContent
{
    public const MEDIA_SCREEN = "screen";

    private InlineCssCollection $inline_css;
    private OnLoadCodeCollection $on_load_code;
    private JsCollection $js;
    private CssCollection $css;
    private ?TagCollection $og_meta_data = null;
    /**
     * @var Html\Tag[]
     */
    private array $meta_data = [];
    private string $base_url = "";
    private string $text_direction;

    public function __construct(
        protected string $resource_version,
        protected bool $append_resource_version = true,
        protected bool $strip_queries = false,
        protected bool $allow_external = true,
        protected bool $allow_non_existing = false,
    ) {
        $this->reset();
    }

    public function reset(): void
    {
        $this->css = new CssCollection(
            $this->resource_version,
            $this->append_resource_version,
            $this->strip_queries,
            $this->allow_external,
            $this->allow_non_existing
        );
        $this->js = new JsCollection(
            $this->resource_version,
            $this->append_resource_version,
            $this->strip_queries,
            $this->allow_external,
            $this->allow_non_existing
        );
        $this->on_load_code = new OnLoadCodeCollection(
            $this->resource_version,
            false,
            true,
            false,
            false
        );
        $this->inline_css = new InlineCssCollection(
            $this->resource_version,
            false,
            true,
            false,
            false
        );
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

    public function addOpenGraphMetaDatum(TagCollection $og_meta_data): void
    {
        $this->og_meta_data = $og_meta_data;
    }

    public function addMetaDatum(Tag $meta_data): void
    {
        if ($meta_data instanceof TagCollection || $meta_data instanceof OpenGraph\Tag) {
            throw new \LogicException(
                sprintf(
                    'Please use %s::addOpenGraphMetaDatum to add open-graph metadata.',
                    self::class
                )
            );
        }

        // keep user-defined keys unique, there should be no case where
        // multiple of the same keys are required.
        if ($meta_data instanceof UserDefined) {
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

    public function getOpenGraphMetaData(): ?TagCollection
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
