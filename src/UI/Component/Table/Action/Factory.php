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

namespace ILIAS\UI\Component\Table\Action;

use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

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
     * @return \ILIAS\UI\Component\Table\Action\Standard
     */
    public function standard(
        string $label,
        URLBuilder $url_builder,
        URLBuilderToken $row_id_parameter
    ): Standard;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Single Action applies to a single record only.
     *       A typical example would be "edit".
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Action\Single
     */
    public function single(
        string $label,
        URLBuilder $url_builder,
        URLBuilderToken $row_id_parameter
    ): Single;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Multi Action can only be used with more than one record.
     *       A typical example would be "compare".
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Action\Multi
     */
    public function multi(
        string $label,
        URLBuilder $url_builder,
        URLBuilderToken $row_id_parameter
    ): Multi;
}
