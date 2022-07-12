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
 
namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Refinery\Constraint;
use Closure;
use ILIAS\Refinery\Factory;
use ILIAS\Data\Factory as DataFactory;

/**
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class Hidden extends Input implements \ILIAS\UI\Component\Input\Field\Hidden
{
    public function __construct(DataFactory $data_factory, Factory $refinery)
    {
        parent::__construct($data_factory, $refinery, '', null);
    }

    public function getUpdateOnLoadCode() : Closure
    {
        return static function () {
        };
    }

    protected function getConstraintForRequirement() : ?Constraint
    {
        return null;
    }

    protected function isClientSideValueOk($value) : bool
    {
        return true;
    }
}
