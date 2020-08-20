<?php
declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery;

use ILIAS\Refinery\In;
use ILIAS\Refinery\To;

class Factory
{
    /**
     * @var \ILIAS\Data\Factory
     */
    private $dataFactory;

    /**
     * @var \ilLanguage
     */
    private $language;

    /**
     * @param \ILIAS\Data\Factory $dataFactory
     * @param \ilLanguage $language
     */
    public function __construct(\ILIAS\Data\Factory $dataFactory, \ilLanguage $language)
    {
        $this->dataFactory = $dataFactory;
        $this->language = $language;

        $this->language->loadLanguageModule('validation');
    }

    /**
     * Combined validations and transformations for primitive data types that
     * establish a baseline for further constraints and more complex transformations
     *
     * @return To\Group
     */
    public function to() : To\Group
    {
        return new To\Group($this->dataFactory);
    }

    /**
     * Combined validations and transformations for primitive data types that
     * establish a baseline for further constraints and more complex transformations.
     *
     * Other then the `to`-group, the `kindlyTo` transformation attempts to implement
     * [Postels Law](https://en.wikipedia.org/wiki/Robustness_principle) by being
     * reasonably liberal when interpreting data. Look into the various transformations
     * in the group for detailed information what works exactly.
     *
     * @return KindlyTo\Group
     */
    public function kindlyTo() : To\Group
    {
        return new KindlyTo\Group($this->dataFactory);
    }

    /**
     * Creates a factory object to create a transformation object, that
     * can be used to execute other transformation objects in a desired
     * order.
     *
     * @return In\Group
     */
    public function in() : In\Group
    {
        return new In\Group();
    }

    /**
     * Contains constraints and transformations on numbers. Each constraint
     * on an int will attempt to transform to int as well.
     *
     * @return Integer\Group
     */
    public function int() : Integer\Group
    {
        return new Integer\Group($this->dataFactory, $this->language);
    }

    /**
     * Contains constraints for string
     *
     * @return String\Group
     */
    public function string() : String\Group
    {
        return new String\Group($this->dataFactory, $this->language);
    }

    /**
     * Contains constraints and transformations for custom functions.
     *
     * @return Custom\Group
     */
    public function custom() : Custom\Group
    {
        return new Custom\Group($this->dataFactory, $this->language);
    }

    /**
     * Contains constraints for container types (e.g. arrays)
     *
     * @return Container\Group
     */
    public function container()
    {
        return new Container\Group($this->dataFactory, $this->language);
    }

    /**
     * Contains constraints for password strings
     *
     * @return Password\Group
     */
    public function password()
    {
        return new Password\Group($this->dataFactory, $this->language);
    }

    /**
     * Contains constraints for logical compositions with other constraints
     *
     * @return Logical\Group
     */
    public function logical()
    {
        return new Logical\Group($this->dataFactory, $this->language);
    }

    /**
     * Contains constraints for null types
     *
     * @return IsNull
     */
    public function null()
    {
        return new IsNull($this->dataFactory, $this->language);
    }

    /**
     * Contains constraints for numeric data types
     *
     * @return Numeric\Group
     */
    public function numeric()
    {
        return new Numeric\Group($this->dataFactory, $this->language);
    }

    /**
     * Contains transformations for DateTime
     *
     * @return DateTime\Group
     */
    public function dateTime()
    {
        return new DateTime\Group();
    }

    /**
     * Contains transformations for Data\URI
     *
     * @return URI\Group
     */
    public function uri() : URI\Group
    {
        return new URI\Group();
    }
}
