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

namespace ILIAS\Refinery\Custom;

use ILIAS\Refinery\Constraint as ConstraintInterface;
use ILIAS\Refinery\DeriveTransformFromApplyTo;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Data;
use ILIAS\Data\Result;
use ILIAS\Refinery\ProblemBuilder;
use ilLanguage;

class Constraint implements ConstraintInterface
{
    use DeriveTransformFromApplyTo;
    use DeriveInvokeFromTransform;
    use ProblemBuilder;

    protected Data\Factory $data_factory;
    protected ilLanguage $lng;
    /** @var callable */
    protected $is_ok;
    /** @var callable|string */
    protected $error;

    /**
     * If $error is a callable it needs to take two parameters:
     *      - one callback $txt($lng_id, ($value, ...)) that retrieves the lang var
     *        with the given id and uses sprintf to replace placeholder if more
     *        values are provide.
     *      - the $value for which the error message should be build.
     *
     * @param callable $is_ok
     * @param string|callable $error
     * @param Data\Factory $data_factory
     * @param ilLanguage $lng
     */
    public function __construct(callable $is_ok, $error, Data\Factory $data_factory, ilLanguage $lng)
    {
        $this->is_ok = $is_ok;
        $this->error = $error;
        $this->data_factory = $data_factory;
        $this->lng = $lng;
    }

    /**
     * @inheritDoc
     */
    protected function getError()
    {
        return $this->error;
    }

    /**
     * @inheritDoc
     */
    final public function check($value)
    {
        if (!$this->accepts($value)) {
            throw new \UnexpectedValueException($this->getErrorMessage($value));
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    final public function accepts($value) : bool
    {
        return call_user_func($this->is_ok, $value);
    }

    /**
     * @inheritDoc
     */
    final public function problemWith($value) : ?string
    {
        if (!$this->accepts($value)) {
            return $this->getErrorMessage($value);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    final public function applyTo(Result $result) : Result
    {
        if ($result->isError()) {
            return $result;
        }

        $problem = $this->problemWith($result->value());
        if ($problem !== null) {
            $error = $this->data_factory->error($problem);
            return $error;
        }

        return $result;
    }
}
