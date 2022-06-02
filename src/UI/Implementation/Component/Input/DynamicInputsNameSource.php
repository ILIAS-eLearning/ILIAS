<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\Input;

/**
 * Other than the FormInputNameSource this name source is for inputs
 * that can be dynamically added multiple times on clientside,
 * therefore it must provide a name that is stacked when submitted to
 * the backend.
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class DynamicInputsNameSource extends FormInputNameSource
{
    protected string $parent_input_name;

    public function __construct(string $parent_input_name)
    {
        $this->parent_input_name = $parent_input_name;
    }

    public function getNewName() : string
    {
        return "$this->parent_input_name[" . parent::getNewName() . "][]";
    }
}
