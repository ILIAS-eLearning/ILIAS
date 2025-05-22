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

namespace ILIAS\Data\Text;

use ILIAS\Data\Text\HTML;
use ILIAS\Data\Text\PlainText;

/**
 * Methods in this interface should mostly be called by the according methods
 * on `Text` instances, most consumer code shouldn't need to bother with `Shape`
 * directly.
 *
 * Only exception is `fromString` which acts as an entrypoint into the logic
 * of Text.
 */
interface Shape
{
    /**
     * @throws \InvalidArgumentException if $text is not compliant.
     */
    public function fromString(string $text): Text;

    public function isRawStringCompliant(string $text): bool;
    /**
     * It should almost never be required to call this directly on shape. There is
     * an according method on the `Text` interface, which should be prefered as it
     * guarantees to use the correct `Shape`. This method still is on `Shape` for
     * code organisation reasons: `Shape` binds all transformations between formats
     * together.
     *
     * @throws \LogicException if $text does not match this shape.
     */
    public function toHTML(Text $text): HTML;
    /**
     * It should almost never be required to call this directly on shape. There is
     * an according method on the `Text` interface, which should be prefered as it
     * guarantees to use the correct `Shape`. This method still is on `Shape` for
     * code organisation reasons: `Shape` binds all transformations between formats
     * together.
     *
     * @throws \LogicException if $text does not match this shape.
     */
    public function toPlainText(Text $text): PlainText;
    public function getMarkup(): Markup;
}
