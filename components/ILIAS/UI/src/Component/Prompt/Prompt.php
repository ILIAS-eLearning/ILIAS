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

namespace ILIAS\UI\Component\Prompt;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Signal;
use ILIAS\Data\URI;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\URLBuilder;

interface Prompt extends Component, JavaScriptBindable
{
    /**
     * Get a Prompt like this, but provide it with an initial title.
     */
    public function withTitle(string $title): static;

    /**
     * Get a Prompt like this, but alter a parameter of the URLBuilder
     * with another value. The parameter is identified using a token.
     *
     * This alters the Signal returned by {@see self::getShowSignal()}.
     */
    public function withParameter(URLBuilderToken $token, string|array $value): static;

    /**
     * Get the URLBuilder used to load Prompt State's for this Prompt.
     *
     * This is on the public interface so other UI components can access
     * the endpoint of the Prompt as well.
     */
    public function getUrlBuilder(): URLBuilder;

    /**
     * Get the Signal to show this Prompt and load its Prompt State.
     *
     * Alter this Signal using {@see self::withParameter()}.
     */
    public function getShowSignal(): Signal;

    /**
     * Get the Signal to close this Prompt.
     */
    public function getCloseSignal(): Signal;
}
