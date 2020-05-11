<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nhaagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table\Action;

interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *       The Standard Action applies to both a single and multiple records.
     *       A typical example would be "delete".
     *
     * ---
     * @param string $label
     * @param string $parameter_name
     * @param Data\URI|UI\Component\Signal $target
     * @return \ILIAS\UI\Component\Table\Action\Action
     */
    public function standard(
        string $label,
        string $parameter_name,
        $target
    ) : Standard;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Single Action applies to a single record only.
     *       A typical example would be "edit".
     *
     * ---
     * @param string $label
     * @param string $parameter_name
     * @param Data\URI|UI\Component\Signal $target
     * @return \ILIAS\UI\Component\Table\Action\Action
     */
    public function single(
        string $label,
        string $parameter_name,
        $target
    ) : Single;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Multi Action can only be used with more than one record.
     *       A typical example would be "compare".
     *
     * ---
     * @param string $label
     * @param string $parameter_name
     * @param Data\URI|UI\Component\Signal $target
     * @return \ILIAS\UI\Component\Table\Action\Action
     */
    public function multi(
        string $label,
        string $parameter_name,
        $target
    ) : Multi;
}
