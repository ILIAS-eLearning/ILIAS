<?php declare(strict_types=1);

use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

trait ilForumRequestTrait
{
    private function retrieveIntOrZeroFrom(ArrayBasedRequestWrapper $wrapper, string $param) : int
    {
        $value = 0;
        if ($wrapper->has($param)) {
            $value = $wrapper->retrieve(
                $param,
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->int(),
                    $this->refinery->custom()->transformation(static function ($value) : int {
                        if ($value === '') {
                            return 0;
                        }

                        return $value;
                    })
                ])
            );
        }

        return $value;
    }
}
