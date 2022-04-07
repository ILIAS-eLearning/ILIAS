<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function toNewObject()
{
    class SomeOtherClass
    {
        private string $firstParameter;
        private int $secondParameter;
        private string $thirdParameter;

        public function __construct(
            string $firstParameter,
            int $secondParameter,
            string $thirdParameter
        ) {
            $this->firstParameter = $firstParameter;
            $this->secondParameter = $secondParameter;
            $this->thirdParameter = $thirdParameter;
        }

        public function say() : string
        {
            return $this->firstParameter;
        }
        
        public function getFirstParameter() : string
        {
            return $this->firstParameter;
        }

        public function getSecodParameter() : string
        {
            return $this->secondParameter;
        }

        public function getThirdParameter() : string
        {
            return $this->thirdParameter;
        }
    }
    
    global $DIC;

    $refinery = $DIC->refinery();

    $transformation = $refinery->to()->toNew(SomeOtherClass::class);

    $result = $transformation->transform(['firstParameter', 2, 'thirdParameter']);

    return assert('firstParameter' === $result->getFirstParameter());
}
