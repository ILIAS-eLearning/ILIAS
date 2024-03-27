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
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Input\InputData;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\Input\PostDataFromServerRequest;
use ILIAS\UI\Implementation\Component\Input\NameSource;

/**
 * This implements commonalities between all forms.
 */
abstract class Form extends Container implements C\Input\Container\Form\Form
{
    /**
     * @param C\Input\Container\Form\FormInput[] $inputs
     */
    public function __construct(
        C\Input\Field\Factory $field_factory,
        NameSource $name_source,
        array $inputs
    ) {
        parent::__construct($name_source);
        $this->setInputGroup($field_factory->group($inputs)->withDedicatedName('form'));
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

    /**
     * @inheritDoc
     */
    protected function extractRequestData(ServerRequestInterface $request): InputData
    {
        return new PostDataFromServerRequest($request);
    }
}
