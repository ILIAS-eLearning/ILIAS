<?php declare(strict_types=1);


namespace ILIAS\UI\Component\Legacy;

interface Factory
{
    /**
     * @param string $content the content of the legacy component
     * @return \ILIAS\UI\Component\Legacy\Legacy
     */
    public function legacy(string $content) : Legacy;
}
