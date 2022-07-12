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

namespace ILIAS\Refinery;

use Exception;
use ILIAS\Data;

class ByTrying implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;
    use ProblemBuilder;

    /** @var Transformation[] */
    private array $transformations;
    private Data\Factory $data_factory;
    /** @var callable */
    private $error;

    /**
     * @param Transformation[] $transformations
     * @param Data\Factory $data_factory
     */
    public function __construct(array $transformations, Data\Factory $data_factory)
    {
        $this->transformations = $transformations;
        $this->data_factory = $data_factory;
        $this->error = static function () : void {
            throw new ConstraintViolationException(
                'no valid constraints',
                'no_valid_constraints'
            );
        };
    }

    /**
     * @inheritDoc
     */
    protected function getError() : callable
    {
        return $this->error;
    }

    /**
     * @inheritDoc
     */
    public function transform($from)
    {
        foreach ($this->transformations as $transformation) {
            $result = $this->data_factory->ok($from);
            $result = $transformation->applyTo($result);
            if ($result->isOK()) {
                return $result->value();
            }
        }
        throw new Exception($this->getErrorMessage($from));
    }
}
