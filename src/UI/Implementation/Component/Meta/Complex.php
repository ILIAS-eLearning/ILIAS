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
 */

namespace ILIAS\UI\Implementation\Component\Meta;

use ILIAS\UI\Component\Meta\Complex as ComplexMeta;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Complex extends AbstractMeta implements ComplexMeta
{
    protected string $content;

    public function __construct(
        string $key,
        string $value,
        string $content
    ) {
        parent::__construct($key, $value);

        $this->checkEmptyStringArg('content', $content);
        $this->content = $content;
    }

    public function getContent() : string
    {
        return $this->content;
    }

    /**
     * @return string[]
     */
    protected function getSupportedAttributes() : array
    {
        return [
            self::ATTRIBUTE_NAME,
            self::ATTRIBUTE_PROPERTY,
            self::ATTRIBUTE_HTTP_EQUIV,
            self::ATTRIBUTE_ITEM_PROP,
        ];
    }
}
