<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function toNewObject()
{
    class SomeOtherClass
    {
        private $firstParameter;
        private $secondParameter;
        private $thirdParameter;

        public function __construct(
            string $firstParameter,
            int $secondParameter,
            string $thirdParameter
        ) {
            $this->firstParameter = $firstParameter;
            $this->secondParameter = $secondParameter;
            $this->thirdParameter = $thirdParameter;
        }

        public function say()
        {
            return $this->firstParameter;
        }
    }

    global $DIC;

    $refinery = $DIC->refinery();

    $transformation = $refinery->to()->toNew(
        'SomeOtherClass'
    );

    $result = $transformation->transform(array('firstParameter', 2, 'thirdParameter'));

    return assert('firstParameter' === $result->say());
}
