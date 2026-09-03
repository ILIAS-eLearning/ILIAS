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

namespace ILIAS\GlobalScreen\GUI\Flow;

use ILIAS\HTTP\Services;
use ILIAS\Data\URI;
use ILIAS\GlobalScreen\GUI\I18n\MultiLanguageGUI;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal Please do not use outside GlobalScreen
 */
class Flow
{
    public function __construct(
        private Services $http,
        private \ilCtrlInterface $ctrl
    ) {
    }

    public function ctrl(): \ilCtrlInterface
    {
        return $this->ctrl;
    }

    public function redirect(string $command, ?string $target_class = null): never
    {
        $target_class ??= $this->getCurrentClass();

        $this->ctrl->redirectByClass($target_class, $command);
    }

    public function getHereAsFlow(): array
    {
        $call_history = $this->ctrl->getCallHistory();
        return array_map(static fn(array $c): string => $c['cmdClass'], $call_history);
    }

    public function getParentAsURI(?string $cmd = null): URI
    {
        $this->ctrl->getCallHistory();
        $command_classes = $this->getHereAsFlow();
        // remove the last class
        array_pop($command_classes);

        return $this->getTargetURI($command_classes, $cmd);
    }

    public function getHereAsURI(?string $cmd = null): URI
    {
        $uri = new URI((string) $this->http->request()->getUri());
        if ($cmd !== null) {
            return $uri->withParameter('cmd', $cmd);
        }
        return $uri;
    }

    public function getTranslationAsURI(?string $command = null): URI
    {
        $classes = array_map(static fn(array $c): string => $c['cmdClass'], $this->ctrl->getCallHistory());

        $classes[] = MultiLanguageGUI::class;

        return $this->getTargetURI(MultiLanguageGUI::class, $command);
    }

    public function getURI(string $with_path): URI
    {
        return new URI(rtrim(ILIAS_HTTP_PATH, '/') . '/' . ltrim($with_path, '/'));
    }

    /**
     * @deprecated use getTargetURI instead
     */
    public function getLinkTarget(object $target_class, ?string $command = null): string
    {
        return $this->ctrl->getLinkTarget($target_class, $command);
    }

    public function getCurrentClass(): ?string
    {
        return $this->ctrl->getCmdClass();
    }

    /**
     * @deprecated use getTargetURI instead
     */

    public function getTargetByClass(array|string $target_class, ?string $command = null): string
    {
        return $this->ctrl->getLinkTargetByClass($target_class, $command);
    }

    public function getTargetURI(object|array|string $target_class, ?string $command = null): URI
    {
        if (is_object($target_class)) {
            $target_class = $target_class::class;
        }

        return $this->getURI($this->getTargetByClass($target_class, $command));
    }

    public function getCommand(?string $fallback = null): string
    {
        $cmd = $this->ctrl->getCmd($fallback);
        return empty($cmd) ? ($fallback ?? '') : $cmd;
    }

}
