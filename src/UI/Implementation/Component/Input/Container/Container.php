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

namespace ILIAS\UI\Implementation\Component\Input\Container;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as CI;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\ServerRequestInterface;
use LogicException;

/**
 * This implements commonalities between all forms.
 */
abstract class Container implements C\Input\Container\Container
{
    use ComponentHelper;

    protected const NESTED_INPUT_LIMIT = 25;

    protected C\Input\Field\Group $input_group;
    protected ?Transformation $transformation;
    protected ?string $error = null;
    protected ?string $dedicated_name = null;
    protected CI\Input\NameSource $name_source;

    /**
     * For the implementation of NameSource.
     */
    public function __construct(
        FieldFactory $field_factory,
        NameSource $name_source,
        array $inputs
    ) {
        $this->checkArgListInputs("input", $inputs, $this->getAllowedInputs());

        $this->name_source = clone $name_source;
        $this->input_group = $field_factory->group(
            $inputs
        )
       ->withDedicatedName('form')
       ->withNameFrom($name_source);

        $this->transformation = null;
    }

    /**
     * @inheritdoc
     */
    public function getInputs(): array
    {
        return $this->getInputGroup()->getInputs();
    }

    public function getInputGroup(): C\Input\Field\Group
    {
        return $this->input_group;
    }

    /**
     * @inheritdoc
     */
    public function withRequest(ServerRequestInterface $request): self
    {
        $post_data = $this->extractRequestData($request);

        $clone = clone $this;
        $clone->input_group = $this->getInputGroup()->withInput($post_data);

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalTransformation(Transformation $trafo): self
    {
        $clone = clone $this;
        $clone->input_group = $this->getInputGroup()->withAdditionalTransformation($trafo);

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    protected function setError(string $error): void
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

    public function getDedicatedName(): ?string
    {
        return $this->dedicated_name;
    }

    public function withDedicatedName(string $dedicated_name): self
    {
        $clone = clone $this;
        $clone->dedicated_name = $dedicated_name;
        $clone->input_group = $clone->input_group
            ->withDedicatedName($dedicated_name)
            ->withNameFrom($clone->name_source);
        return $clone;
    }

    /**
     * Checks the given array recursively for elements of the given classes.
     */
    protected function checkArgListInputs(string $which, array $values, array $classes, int $depth = 0): void
    {
        if (self::NESTED_INPUT_LIMIT < $depth) {
            throw new LogicException("Input nesting limit of " . self::NESTED_INPUT_LIMIT . " exceeded.");
        }

        foreach ($values as $input) {
            if ($input instanceof C\Input\Field\Group) {
                $this->checkArgListInputs($which, $input->getInputs(), $classes, $depth + 1);
                continue;
            }

            foreach ($classes as $class) {
                $this->checkArgInstanceOf($which, $input, $class);
            }
        }
    }

    /**
     * Returns the extracted data from the given server request. This methods has been introduced
     * since different containers may allow different request methods.
     */
    abstract protected function extractRequestData(ServerRequestInterface $request): InputData;

    /**
     * Returns a list of container-specific inputs, which are checked against when creating a new
     * container. Please try to use the most generic input-interfaces possible.
     *
     * @return string[]
     */
    abstract protected function getAllowedInputs(): array;
}
