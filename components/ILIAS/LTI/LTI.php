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

use ArrayAccess;
use Closure;
use ILIAS\DI\Container;
use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Services as GlobalScreen;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Language\ComponentTranslation\ComponentLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\LTI\View as LTIView;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Setup\Agent;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ilAuthSession;
use ilCtrl;
use ilLanguage;
use ilLTISetupAgent;
use ilLTIViewGUI;
use ilObjUser;
use ReflectionClass;

/**
 * LTI component: LTI 1.1 (classes/LTI1p1) and LTI Advantage (classes/LTIAdvantage) for ILIAS as consumer and provider.
 * See README.md for the structure.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class LTI implements Component\Component
{
    public function init(
        array | ArrayAccess &$define,
        array | ArrayAccess &$implement,
        array | ArrayAccess &$use,
        array | ArrayAccess &$contribute,
        array | ArrayAccess &$seek,
        array | ArrayAccess &$provide,
        array | ArrayAccess &$pull,
        array | ArrayAccess &$internal,
    ): void {
        $define[] = LTIView::class;

        $implement[LTIView::class] = static fn() => new ilLTIViewGUI(
            $use[GlobalHttpState::class],
            $pull[Refinery::class],
            $use[UIFactory::class],
            $use[UIRenderer::class],
            self::legacy(UIServices::class, static fn(Container $dic): UIServices => $dic->ui()),
            self::legacy(ilCtrl::class, static fn(Container $dic): ilCtrl => $dic['ilCtrl']),
            self::legacy(ilObjUser::class, static fn(Container $dic): ilObjUser => $dic['ilUser']),
            self::legacy(ilLanguage::class, static fn(Container $dic): ilLanguage => $dic['lng']),
            self::legacy(GlobalScreen::class, static fn(Container $dic): GlobalScreen => $dic['global_screen']),
            self::legacy(ilAuthSession::class, static fn(Container $dic): ilAuthSession => $dic['ilAuthSession']),
        );

        $contribute[Agent::class] = static fn() => new ilLTISetupAgent($pull[Refinery::class]);

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
        $contribute[LanguageFileDirectory::class] = fn() => new ComponentLanguageFileDirectory(
            $this,
            'lti'
        );
    }

    /**
     * A service the legacy initialisation still sets up, which the LTI view only gets once it uses it.
     *
     * @template T of object
     * @param class-string<T> $class
     * @param Closure(Container): T $get
     * @return T
     */
    private static function legacy(string $class, Closure $get): object
    {
        return new ReflectionClass($class)->newLazyProxy(static function () use ($get): object {
            global $DIC;

            return $get($DIC);
        });
    }
}
