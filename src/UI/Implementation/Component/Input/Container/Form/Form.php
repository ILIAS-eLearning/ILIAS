<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as CI;
use ILIAS\UI\Implementation\Component\Input;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\ServerRequestInterface;
use LogicException;

/**
 * This implements commonalities between all forms.
 */
abstract class Form implements C\Input\Container\Form\Form
{
    use ComponentHelper;

    protected C\Input\Field\Group $input_group;
    protected ?Transformation $transformation;
    protected ?string $error = null;

    /**
     * For the implementation of NameSource.
     */
    public function __construct(
        Input\Field\Factory $field_factory,
        Input\NameSource $name_source,
        array $inputs
    ) {
        $classes = [CI\Input\Field\Input::class];
        $this->checkArgListElements("input", $inputs, $classes);
        // TODO: this is a dependency and should be treated as such. `use` statements can be removed then.

        $this->input_group = $field_factory->group(
            $inputs
        )->withNameFrom($name_source);

        $this->transformation = null;
    }

    /**
     * @inheritdoc
     */
    public function getInputs() : array
    {
        return $this->getInputGroup()->getInputs();
    }

    /**
     * @inheritdoc
     */
    public function getInputGroup() : C\Input\Field\Group
    {
        return $this->input_group;
    }

    /**
     * @inheritdoc
     */
    public function withRequest(ServerRequestInterface $request)
    {
        if (!$this->isSanePostRequest($request)) {
            throw new LogicException("Server request is not a valid post request.");
        }
        $post_data = $this->extractPostData($request);

        $clone = clone $this;
        $clone->input_group = $this->getInputGroup()->withInput($post_data);

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalTransformation(Transformation $trafo)
    {
        $clone = clone $this;
        $clone->input_group = $this->getInputGroup()->withAdditionalTransformation($trafo);

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getError() : ?string
    {
        return $this->error;
    }

    protected function setError(string $error) : void
    {
        $this->error = $error;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $content = $this->getInputGroup()->getContent();
        if (!$content->isok()) {
            $this->setError($content->error());
            return null;
        }

        return $content->value();
    }

    /**
     * Check the request for sanity.
     * TODO: implement me!
     */
    protected function isSanePostRequest(ServerRequestInterface $request) : bool
    {
        return true;
    }

    /**
     * Extract post data from request.
     */
    protected function extractPostData(ServerRequestInterface $request) : Input\InputData
    {
        return new PostDataFromServerRequest($request);
    }
}
