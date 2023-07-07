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

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Implementation\Component\Input\Container\Container;
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
abstract class Form extends Container implements C\Input\Container\Form\Form
{
    /**
     * @inheritdoc
     */
    public function withRequest(ServerRequestInterface $request): self
    {
        $post_data = $this->extractPostData($request);

        $clone = clone $this;
        $clone->input_group = $this->getInputGroup()->withInput($post_data);

        return $clone;
    }

    /**
     * Extract post data from request.
     */
    protected function extractPostData(ServerRequestInterface $request): InputData
    {
        return new PostDataFromServerRequest($request);
    }


    public function hasRequiredInputs(): bool
    {
        foreach ($this->getInputs() as $input) {
            if ($input->isRequired()) {
                return true;
            }
        }
        return false;
    }
}
