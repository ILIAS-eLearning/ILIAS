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

namespace ILIAS;

use ILIAS\Language\ComponentTranslation\ComponentLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;

/**
 * LTI component: LTI 1.1 (classes/LTI1p1) and LTI Advantage (classes/LTIAdvantage) for ILIAS as consumer and provider.
 * See README.md for the structure.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class LTI implements Component\Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $define[] = \ILIAS\LTI\View::class;

        $implement[\ILIAS\LTI\View::class] = static fn(): \ILIAS\LTI\View =>
            $internal[\ilLTIViewGUI::class];

        $contribute[\ILIAS\Setup\Agent::class] = static fn(): \ILIAS\Setup\Agent =>
            new \ilLTISetupAgent($pull[\ILIAS\Refinery\Factory::class]);

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "ltiresult.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "lti.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "lticerts.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "ltiauth.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "ltitoken.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "lticonfig.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "ltiregistration.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "ltitoolregistration.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "ltiservices.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentCSS($this, "lti_creation.css");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentCSS($this, "lti_view.css");

        $contribute[LanguageFileDirectory::class] = fn() => new ComponentLanguageFileDirectory(
            $this,
            'ltiv'
        );

        $internal[\ilLTIViewGUI::class] = static fn(): \ilLTIViewGUI =>
            new \ilLTIViewGUI(
                $use[\ILIAS\HTTP\GlobalHttpState::class],
                $pull[\ILIAS\Refinery\Factory::class],
                $use[\ILIAS\UI\Factory::class],
                $use[\ILIAS\UI\Renderer::class],
                $use[\ILIAS\UICore\GlobalTemplate::class],
                $use[\ILIAS\Language\Language::class],
                $internal[\ILIAS\LTI\View\CurrentUser::class],
                $internal[\ILIAS\LTI\View\Navigation::class],
                $internal[\ILIAS\LTI\View\ScreenContext::class],
                $internal[\ILIAS\LTI\View\AuthSession::class],
            );

        $internal[\ILIAS\LTI\View\CurrentUser::class] = static fn(): \ILIAS\LTI\View\CurrentUser =>
            new \ILIAS\LTI\View\CurrentUserLegacyProxy();

        $internal[\ILIAS\LTI\View\Navigation::class] = static fn(): \ILIAS\LTI\View\Navigation =>
            new \ILIAS\LTI\View\NavigationLegacyProxy();

        $internal[\ILIAS\LTI\View\ScreenContext::class] = static fn(): \ILIAS\LTI\View\ScreenContext =>
            new \ILIAS\LTI\View\ScreenContextLegacyProxy();

        $internal[\ILIAS\LTI\View\AuthSession::class] = static fn(): \ILIAS\LTI\View\AuthSession =>
            new \ILIAS\LTI\View\AuthSessionLegacyProxy();
    }
}
