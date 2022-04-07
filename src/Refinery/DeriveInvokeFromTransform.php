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
use InvalidArgumentException;

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 */
trait DeriveInvokeFromTransform
{
    /**
     * @param mixed $from
     * @return mixed
     * @throws Exception
     */
    abstract public function transform($from);

    /**
     * @throws InvalidArgumentException  if the argument could not be transformed
     * @param  mixed  $from
     * @return mixed
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }
}
