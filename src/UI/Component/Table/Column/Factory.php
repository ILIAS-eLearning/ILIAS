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
 
namespace ILIAS\UI\Component\Table\Column;

interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *       The Text Column is used for (short) text.
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Column\Text
     */
    public function text(string $title) : Text;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Number Column is used for numeric values.
     * ---
     * @return \ILIAS\UI\Component\Table\Column\Number
     */
    public function number(string $title) : Number;
}
