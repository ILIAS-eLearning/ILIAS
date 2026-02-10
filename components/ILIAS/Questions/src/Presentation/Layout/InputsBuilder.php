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

namespace ILIAS\Questions\Presentation\Layout;

use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Definitions\EnvironmentImplementation;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Custom\Transformation as CustomTransformation;
use ILIAS\UI\Component\Input\Input;

/**
 * This is a InputBuilder for multipart forms. It allows you to carry the necessary
 * information from one page of the form to the next in the query. Be aware that
 * you are thoroughly limited in the amount of information that you can carry over,
 * so make sure to just set the minimum you absolutely need.
 */
class InputsBuilder
{
    private ?string $carry = null;

    /**
     * @param CustomTransformation $to_inputs This Transformation receives the
     * value set by `self::withCarry()` if it is available, otherwise the value
     * will be null.
     */
    public function __construct(
        private readonly Refinery $refinery,
        private readonly CustomTransformation $to_inputs
    ) {
    }

    /**
     * @param string $carry The `string` will be `base64-encoded` before
     * adding it to the `Query`. The string will be passed to the `$to_inputs`
     * transformation to allow the recreation of the inputs.
     */
    public function withCarry(
        string $carry
    ): self {
        $clone = clone $this;
        $clone->carry = $carry;
        return $clone;
    }

    public function addCarryToEnvironment(
        EnvironmentImplementation $environment
    ): Environment {
        return $environment->withCarryParameter(
            $this->carry === null
                ? $environment->getCarry($this->refinery->identity())
                : base64_encode($this->carry)
        );
    }

    /**
     * @return \ILIAS\UI\Component\Input\Input|array|null The return value will
     * be null, if no inputs could be built. This is a sign that something is
     * wrong with carry. This might mean that there is a mistake in the carry
     * that was set, but it can also be the consequence of somebody messing
     * with the query.
     */
    public function getInputs(
        EnvironmentImplementation $environment
    ): Input|array|null {
        return $environment->getCarry(
            $this->refinery->custom()->transformation(
                fn(?string $v): Input|array|null => $this->to_inputs->transform(
                    $v === null
                        ? null
                        : base64_decode($v)
                )
            )
        );
    }
}
