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

use ILIAS\Refinery\Random\Group as RandomGroup;

class Factory
{
    private \ILIAS\Data\Factory $dataFactory;
    private \ILIAS\Language\Language $language;

    public function __construct(\ILIAS\Data\Factory $dataFactory, \ILIAS\Language\Language $language)
    {
        $this->dataFactory = $dataFactory;
        $this->language = $language;
    }

    /**
     * Combined validations and transformations for primitive data types that
     * establish a baseline for further constraints and more complex transformations
     */
    public function to(): To\Group
    {
        $this->loadLanguageModules();
        return new To\Group($this->dataFactory, $this->language);
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
        return new KindlyTo\Group($this->dataFactory);
    }

    /**
     * Creates a factory object to create a transformation object, that
     * can be used to execute other transformation objects in a desired
     * order.
     */
    public function in(): In\Group
    {
        return new In\Group();
    }

    /**
     * Contains constraints and transformations on numbers. Each constraint
     * on an int will attempt to transform to int as well.
     */
    public function int(): Integer\Group
    {
        $this->loadLanguageModules();
        return new Integer\Group($this->dataFactory, $this->language, $this->in());
    }

    /**
     * Contains constraints for string
     */
    public function string(): String\Group
    {
        $this->loadLanguageModules();
        return new String\Group($this->dataFactory, $this->language);
    }

    /**
     * Contains constraints and transformations for custom functions.
     */
    public function custom(): Custom\Group
    {
        $this->language->loadLanguageModule('validation');
        return new Custom\Group($this->dataFactory, $this->language);
    }

    /**
     * Contains constraints for container types (e.g. arrays)
     */
    public function container(): Container\Group
    {
        $this->loadLanguageModules();
        return new Container\Group($this->dataFactory);
    }

    /**
     * Contains constraints for password strings
     */
    public function password(): Password\Group
    {
        $this->loadLanguageModules();
        return new Password\Group($this->dataFactory, $this->language);
    }

    /**
     * Contains constraints for logical compositions with other constraints
     */
    public function logical(): Logical\Group
    {
        $this->loadLanguageModules();
        return new Logical\Group($this->dataFactory, $this->language);
    }

    /**
     * Contains constraints for null types
     */
    public function null(): Constraint
    {
        $this->loadLanguageModules();
        return new IsNull($this->dataFactory, $this->language);
    }

    /**
     * Contains constraints for numeric data types
     */
    public function numeric(): Numeric\Group
    {
        $this->loadLanguageModules();
        return new Numeric\Group($this->dataFactory, $this->language);
    }

    /**
     * Contains transformations for DateTime
     */
    public function dateTime(): DateTime\Group
    {
        return new DateTime\Group();
    }

    /**
     * Contains transformations for Data\URI
     */
    public function uri(): URI\Group
    {
        return new URI\Group();
    }

    public function encode(): Encode\Group
    {
        return new Encode\Group();
    }

    /**
     * Accepts Transformations and uses first successful one.
     * @param Transformation[] $transformations
     */
    public function byTrying(array $transformations): ByTrying
    {
        $this->loadLanguageModules();
        return new ByTrying($transformations, $this->dataFactory, $this->language);
    }

    public function random(): RandomGroup
    {
        return new RandomGroup();
    }

    public function identity(): Transformation
    {
        return new IdentityTransformation();
    }

    public function always($value): Transformation
    {
        return new ConstantTransformation($value);
    }

    public function executable(): Transformation
    {
        $this->loadLanguageModules();
        return new IsExecutableTransformation($this->language);
    }

    protected function loadLanguageModules(): void
    {
        $this->language->loadLanguageModule('validation');
    }
}
