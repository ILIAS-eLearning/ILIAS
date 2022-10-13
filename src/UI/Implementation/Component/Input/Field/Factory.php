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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;
use ILIAS\Data;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ilLanguage;
use ILIAS\UI\Component\Input\Field\ColorPicker;

/**
 * Class Factory
 *
 * @package ILIAS\UI\Implementation\Component\Input\Field
 */
class Factory implements Field\Factory
{
    protected UploadLimitResolver $upload_limit_resolver;
    protected Data\Factory $data_factory;
    protected SignalGeneratorInterface $signal_generator;
    private \ILIAS\Refinery\Factory $refinery;
    protected ilLanguage $lng;

    public function __construct(
        UploadLimitResolver $upload_limit_resolver,
        SignalGeneratorInterface $signal_generator,
        Data\Factory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        ilLanguage $lng
    ) {
        $this->upload_limit_resolver = $upload_limit_resolver;
        $this->signal_generator = $signal_generator;
        $this->data_factory = $data_factory;
        $this->refinery = $refinery;
        $this->lng = $lng;
    }

    /**
     * @inheritdoc
     */
    public function text(string $label, string $byline = null): Field\Text
    {
        return new Text($this->data_factory, $this->refinery, $label, $byline);
    }

    /**
     * @inheritdoc
     */
    public function numeric(string $label, string $byline = null): Field\Numeric
    {
        return new Numeric($this->data_factory, $this->refinery, $label, $byline);
    }

    /**
     * @inheritdoc
     */
    public function group(array $inputs, string $label = '', $byline = ''): Field\Group
    {
        return new Group($this->data_factory, $this->refinery, $this->lng, $inputs, $label, $byline);
    }

    /**
     * @inheritdoc
     */
    public function optionalGroup(array $inputs, string $label, string $byline = null): Field\OptionalGroup
    {
        return new OptionalGroup($this->data_factory, $this->refinery, $this->lng, $inputs, $label, $byline);
    }

    /**
     * @inheritdoc
     */
    public function switchableGroup(array $inputs, string $label, string $byline = null): Field\SwitchableGroup
    {
        return new SwitchableGroup($this->data_factory, $this->refinery, $this->lng, $inputs, $label, $byline);
    }

    /**
     * @inheritdoc
     */
    public function section(array $inputs, $label, $byline = null): Field\Section
    {
        return new Section($this->data_factory, $this->refinery, $this->lng, $inputs, $label, $byline);
    }

    /**
     * @inheritdoc
     */
    public function checkbox(string $label, string $byline = null): Field\Checkbox
    {
        return new Checkbox($this->data_factory, $this->refinery, $label, $byline);
    }

    /**
     * @inheritDoc
     */
    public function tag(string $label, array $tags, string $byline = null): Field\Tag
    {
        return new Tag($this->data_factory, $this->refinery, $label, $byline, $tags);
    }

    /**
     * @inheritdoc
     */
    public function password(string $label, string $byline = null): Field\Password
    {
        return new Password($this->data_factory, $this->refinery, $label, $byline, $this->signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function select(string $label, array $options, string $byline = null): Field\Select
    {
        return new Select($this->data_factory, $this->refinery, $label, $options, $byline);
    }

    /**
     * @inheritdoc
     */
    public function textarea(string $label, string $byline = null): Field\Textarea
    {
        return new Textarea($this->data_factory, $this->refinery, $label, $byline);
    }

    /**
     * @inheritdoc
     */
    public function radio(string $label, string $byline = null): Field\Radio
    {
        return new Radio($this->data_factory, $this->refinery, $label, $byline);
    }

    /**
     * @inheritdoc
     */
    public function multiSelect(string $label, array $options, string $byline = null): Field\MultiSelect
    {
        return new MultiSelect($this->data_factory, $this->refinery, $label, $options, $byline);
    }

    /**
     * @inheritdoc
     */
    public function dateTime(string $label, string $byline = null): Field\DateTime
    {
        return new DateTime($this->data_factory, $this->refinery, $label, $byline);
    }

    /**
     * @inheritdoc
     */
    public function duration(string $label, string $byline = null): Field\Duration
    {
        return new Duration($this->data_factory, $this->refinery, $this->lng, $this, $label, $byline);
    }

    /**
     * @inheritDoc
     */
    public function file(
        UploadHandler $handler,
        string $label,
        string $byline = null,
        Input $metadata_input = null
    ): Field\File {
        return new File($this->lng, $this->data_factory, $this->refinery, $this->upload_limit_resolver, $handler, $label, $metadata_input, $byline);
    }

    /**
     * @inheritdoc
     */
    public function url(string $label, string $byline = null): Field\Url
    {
        return new Url($this->data_factory, $this->refinery, $label, $byline);
    }

    /**
     * @inheritdoc
     */
    public function link(string $label, string $byline = null): Field\Link
    {
        return new Link($this->data_factory, $this->refinery, $this->lng, $this, $label, $byline);
    }

    /**
     * @inheritDoc
     */
    public function hidden(): Field\Hidden
    {
        return new Hidden($this->data_factory, $this->refinery);
    }

    /**
     * @inheritDoc
     */
    public function colorpicker(string $label, string $byline = null): ColorPicker
    {
        // TODO: Implement colorpicker() method.
        throw new \ILIAS\UI\NotImplementedException();
    }
}
