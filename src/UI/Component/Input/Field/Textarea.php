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
 
namespace ILIAS\UI\Component\Input\Field;

/**
 * This describes Textarea inputs.
 */
interface Textarea extends FormInput
{
    /**
     * set maximum number of characters
     */
    public function withMaxLimit(int $max_limit) : Textarea;

    /**
     * get maximum limit of characters
     * @return mixed
     */
    public function getMaxLimit();

    /**
     * set minimum number of characters
     */
    public function withMinLimit(int $min_limit) : Textarea;

    /**
     * get minimum limit of characters
     * @return mixed
     */
    public function getMinLimit();

    /**
     * bool if textarea has max or min number of character limit.
     */
    public function isLimited() : bool;
}
