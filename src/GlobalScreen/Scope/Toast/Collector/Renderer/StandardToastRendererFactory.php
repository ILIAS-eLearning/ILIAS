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

namespace ILIAS\GlobalScreen\Scope\Toast\Collector\Renderer;

use ILIAS\UI\Component\Component;
use ILIAS\GlobalScreen\Scope\Toast\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Scope\Toast\Factory\isStandardItem;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\GlobalScreen\Client\Notifications as ClientNotifications;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Toast\Factory\StandardToastItem;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class StandardToastRendererFactory implements ToastRendererFactory
{
    protected array $renderers = [];
    protected UIServices $ui;

    public function __construct(UIServices $ui)
    {
        $this->ui = $ui;
    }

    public function getRenderer(string $fully_qualified): ToastRenderer
    {
        $renderer = $this->ensureRenderer($fully_qualified);
        if (!$renderer instanceof \ILIAS\GlobalScreen\Scope\Toast\Collector\Renderer\ToastRenderer) {
            throw new \InvalidArgumentException("Cannot render item of type " . $fully_qualified);
        }
        return $renderer;
    }

    public function buildRenderer(string $fully_qualified): ?ToastRenderer
    {
        $renderer = null;
        switch ($fully_qualified) {
            case isStandardItem::class:
                $renderer = new StandardToastRenderer($this->ui);
                break;
            case StandardToastItem::class:
                $renderer = new StandardToastRenderer($this->ui);
                break;
            default:
                $renderer = null;
                break;
        }
        return $renderer;
    }

    private function ensureRenderer(string $fully_qualified): ?ToastRenderer
    {
        if (!isset($this->renderers[$fully_qualified])) {
            $this->renderers[$fully_qualified] = $this->buildRenderer($fully_qualified);
        }
        return $this->renderers[$fully_qualified];
    }
}
