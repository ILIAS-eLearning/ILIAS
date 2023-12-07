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

namespace ILIAS\Refinery;

use ILIAS\Refinery\In;
use ILIAS\Refinery\To;
use ILIAS\Refinery\Random\Group as RandomGroup;
use ilLanguage;
use ILIAS\Data\Factory as DataFactory;

class Factory
{
    private readonly DataFactory $dataFactory;
    private readonly BuildTransformation $build_transformation;

    public function __construct(DataFactory $dataFactory, ilLanguage $language)
    {
        $this->dataFactory = $dataFactory;
        $this->build_transformation = new BuildTransformation($language);

        $language->loadLanguageModule('validation');
    }

    /**
     * Combined validations and transformations for primitive data types that
     * establish a baseline for further constraints and more complex transformations
     */
    public function to(): To\Group
    {
        return new To\Group($this->dataFactory, $this->build_transformation);
    }

    /**
     * Combined validations and transformations for primitive data types that
     * establish a baseline for further constraints and more complex transformations.
     *
     * Other then the `to`-group, the `kindlyTo` transformation attempts to implement
     * [Postels Law](https://en.wikipedia.org/wiki/Robustness_principle) by being
     * reasonably liberal when interpreting data. Look into the various transformations
     * in the group for detailed information what works exactly.
     */
    public function kindlyTo(): KindlyTo\Group
    {
        return new KindlyTo\Group($this->build_transformation);
    }

    /**
     * Creates a factory object to create a transformation object, that
     * can be used to execute other transformation objects in a desired
     * order.
     */
    public function in(): In\Group
    {
        return new In\Group($this->build_transformation);
    }

    /**
     * Contains constraints and transformations on numbers. Each constraint
     * on an int will attempt to transform to int as well.
     */
    public function int(): Integer\Group
    {
        return new Integer\Group($this->build_transformation);
    }

    /**
     * Contains constraints for string
     */
    public function string(): String\Group
    {
        return new String\Group($this->build_transformation);
    }

    /**
     * Contains constraints and transformations for custom functions.
     */
    public function custom(): Custom\Group
    {
        return new Custom\Group($this->build_transformation);
    }

    /**
     * Contains constraints for container types (e.g. arrays)
     */
    public function container(): Container\Group
    {
        return new Container\Group($this->build_transformation);
    }

    /**
     * Contains constraints for password strings
     */
    public function password(): Password\Group
    {
        return new Password\Group($this->build_transformation);
    }

    /**
     * Contains constraints for logical compositions with other constraints
     */
    public function logical(): Logical\Group
    {
        return new Logical\Group($this->build_transformation);
    }

    /**
     * Contains constraints for numeric data types
     */
    public function numeric(): Numeric\Group
    {
        return new Numeric\Group($this->build_transformation);
    }

    /**
     * Contains transformations for DateTime
     */
    public function dateTime(): DateTime\Group
    {
        return new DateTime\Group($this->build_transformation);
    }

    /**
     * Contains transformations for Data\URI
     */
    public function uri(): URI\Group
    {
        return new URI\Group($this->build_transformation);
    }

    public function random(): RandomGroup
    {
        return new RandomGroup($this->build_transformation);
    }

    /**
     * Contains constraints for null types
     */
    public function null(): Transformation
    {
        return $this->build_transformation->fromConstraint(new IsNull());
    }

    /**
     * Accepts Transformations and uses first successful one.
     * @param Transformable[] $transformations
     */
    public function byTrying(array $transformations): Transformation
    {
        return $this->build_transformation->fromTransformable(new ByTrying($transformations));
    }

    public function identity(): Transformation
    {
        return $this->build_transformation->fromTransformable(new IdentityTransformation());
    }

    public function always($value): Transformation
    {
        return $this->build_transformation->fromTransformable(new ConstantTransformation($value));
    }
}
