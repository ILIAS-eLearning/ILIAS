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

function toNewObject() : bool
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

        public function getSecodParameter() : int
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
