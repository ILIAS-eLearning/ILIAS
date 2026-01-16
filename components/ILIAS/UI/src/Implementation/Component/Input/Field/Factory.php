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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;
use ILIAS\Data;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field as I;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\Language\Language;
use ILIAS\Data\ImagePurpose;

class Factory implements I\Factory
{
    public function __construct(
        protected Node\Factory $node_factory,
        protected UploadLimitResolver $upload_limit_resolver,
        protected SignalGeneratorInterface $signal_generator,
        protected Data\Factory $data_factory,
        protected \ILIAS\Refinery\Factory $refinery,
        protected Language $lng,
    ) {
    }

    public function text(string $label, ?string $byline = null): Text
    {
        return new Text($this->data_factory, $this->refinery, $label, $byline);
    }

    public function numeric(string $label, ?string $byline = null): Numeric
    {
        return new Numeric($this->data_factory, $this->refinery, $label, $byline);
    }

    public function group(array $inputs, string $label = '', ?string $byline = null): Group
    {
        $this->lng->loadLanguageModule('ui');
        return new Group($this->data_factory, $this->refinery, $this->lng, $inputs, $label, $byline);
    }

    public function optionalGroup(array $inputs, string $label, ?string $byline = null): OptionalGroup
    {
        $this->lng->loadLanguageModule('ui');
        return new OptionalGroup($this->data_factory, $this->refinery, $this->lng, $inputs, $label, $byline);
    }

    public function switchableGroup(array $inputs, string $label, ?string $byline = null): SwitchableGroup
    {
        $this->lng->loadLanguageModule('ui');
        return new SwitchableGroup($this->data_factory, $this->refinery, $this->lng, $inputs, $label, $byline);
    }

    public function section(array $inputs, string $label, ?string $byline = null): Section
    {
        $this->lng->loadLanguageModule('ui');
        return new Section($this->data_factory, $this->refinery, $this->lng, $inputs, $label, $byline);
    }

    public function checkbox(string $label, ?string $byline = null): Checkbox
    {
        return new Checkbox($this->data_factory, $this->refinery, $label, $byline);
    }

    public function tag(string $label, array $tags, ?string $byline = null): Tag
    {
        return new Tag($this->data_factory, $this->refinery, $label, $byline, $tags);
    }

    public function password(string $label, ?string $byline = null): Password
    {
        return new Password($this->data_factory, $this->refinery, $label, $byline, $this->signal_generator);
    }

    public function select(string $label, array $options, ?string $byline = null): Select
    {
        return new Select($this->data_factory, $this->refinery, $label, $options, $byline);
    }

    public function textarea(string $label, ?string $byline = null): Textarea
    {
        return new Textarea($this->data_factory, $this->refinery, $label, $byline);
    }

    public function radio(string $label, ?string $byline = null): Radio
    {
        return new Radio($this->data_factory, $this->refinery, $label, $byline);
    }

    public function multiSelect(string $label, array $options, ?string $byline = null): MultiSelect
    {
        return new MultiSelect($this->data_factory, $this->refinery, $label, $options, $byline);
    }

    public function dateTime(string $label, ?string $byline = null): DateTime
    {
        return new DateTime($this->data_factory, $this->refinery, $label, $byline);
    }

    public function duration(string $label, ?string $byline = null): Duration
    {
        $this->lng->loadLanguageModule('ui');
        return new Duration($this->data_factory, $this->refinery, $this->lng, $this, $label, $byline);
    }

    public function file(
        UploadHandler $handler,
        string $label,
        ?string $byline = null,
        ?FormInput $metadata_input = null
    ): File {
        $this->lng->loadLanguageModule('ui');
        return new File(
            $this->lng,
            $this->data_factory,
            $this,
            $this->refinery,
            $this->upload_limit_resolver,
            $handler,
            $label,
            $metadata_input,
            $byline
        );
    }

    public function image(
        UploadHandler $upload_handler,
        ImagePurpose $image_purpose,
        string $label,
        ?string $byline = null,
        ?FormInput $metadata_input = null
    ): Image {
        $this->lng->loadLanguageModule('ui');
        return new Image(
            $this->lng,
            $this->data_factory,
            $this,
            $this->refinery,
            $this->upload_limit_resolver,
            $upload_handler,
            $image_purpose,
            $label,
            $metadata_input,
            $byline,
        );
    }

    public function url(string $label, ?string $byline = null): Url
    {
        return new Url($this->data_factory, $this->refinery, $label, $byline);
    }

    public function link(string $label, ?string $byline = null): Link
    {
        $this->lng->loadLanguageModule('ui');
        return new Link($this->data_factory, $this->refinery, $this->lng, $this, $label, $byline);
    }

    public function hidden(): Hidden
    {
        return new Hidden($this->data_factory, $this->refinery);
    }

    public function colorSelect(string $label, ?string $byline = null): ColorSelect
    {
        return new ColorSelect($this->data_factory, $this->refinery, $label, $byline);
    }

    public function markdown(I\MarkdownRenderer $md_renderer, string $label, ?string $byline = null): Markdown
    {
        return new Markdown($this->data_factory, $this->refinery, $md_renderer, $label, $byline);
    }

    public function rating(string $label, ?string $byline = null): Rating
    {
        return new Rating($this->data_factory, $this->refinery, $label, $byline);
    }

    public function treeSelect(
        I\Node\NodeRetrieval $node_retrieval,
        string $label,
        ?string $byline = null
    ): TreeSelect {
        $this->lng->loadLanguageModule('ui');
        return new TreeSelect(
            $this->lng,
            $this->data_factory,
            $this->refinery,
            $this->hidden(),
            $node_retrieval,
            $label,
            $byline,
        );
    }

    public function treeMultiSelect(
        I\Node\NodeRetrieval $node_retrieval,
        string $label,
        ?string $byline = null,
    ): TreeMultiSelect {
        $this->lng->loadLanguageModule('ui');
        return new TreeMultiSelect(
            $this->lng,
            $this->data_factory,
            $this->refinery,
            $this->hidden(),
            $node_retrieval,
            $label,
            $byline,
        );
    }

    public function node(): Node\Factory
    {
        return $this->node_factory;
    }
}
