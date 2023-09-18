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

namespace ILIAS\UI\Component\Input\Container;

use ILIAS\UI\Component\Component;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Input;

/**
 * This describes commonalities between all Containers for Inputs, such as Forms.
 */
interface Container extends Component
{
    /**
     * Get the inputs contained in the container.
     *
     * @return array<mixed,Input>
     */
    public function getInputs(): array;

    /**
     * Get a form like this where data from the request is attached.
     *
     * @return static
     */
    public function withRequest(ServerRequestInterface $request): self;

    /**
     * Apply a transformation to the data of the form.
     */
    public function withAdditionalTransformation(Transformation $trafo): self;

    /**
     * Get the data in the form if all inputs are ok, where the transformation
     * is applied if one was added. If data was not ok, this will return null.
     *
     * @return mixed|null
     */
    public function getData();

    /**
     * @return null|string
     */
    public function getError(): ?string;

    /**
     * Sets an optional dedicated name for this form which adds a NAME attribute
     * to the form's HTML (otherwise no NAME attribute is set).
     *
     * The dedicated name is inherited by all child inputs of the form
     * by setting it as dedicated name for the top level group that is generated
     * for every form.
     *
     * Please see the description of withDedicatedName() on Field/Input for more details.
     *
     * @param string $dedicated_name
     * @return $this
     */
    public function withDedicatedName(string $dedicated_name): self;
}
