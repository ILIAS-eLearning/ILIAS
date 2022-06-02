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
 
namespace ILIAS\UI\Component\Input\Container\Form;

use ILIAS\UI\Component\Component;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Field\FormInput;

/**
 * This describes commonalities between all forms.
 */
interface Form extends Component
{
    /**
     * Get the inputs contained in the form.
     *
     * @return    array<mixed,FormInput>
     */
    public function getInputs() : array;

    /**
     * Get a form like this where data from the request is attached.
     *
     * @return static
     */
    public function withRequest(ServerRequestInterface $request);

    /**
     * Apply a transformation to the data of the form.
     *
     * @return static
     */
    public function withAdditionalTransformation(Transformation $trafo);

    /**
     * Get the data in the form if all inputs are ok, where the transformation
     * is applied if one was added. If data was not ok, this will return null.
     *
     * @return    mixed|null
     */
    public function getData();

    /**
     * TODO: there should be a further method to attach the different submit buttons
     */

    /**
     * @return null|string
     */
    public function getError() : ?string;
}
