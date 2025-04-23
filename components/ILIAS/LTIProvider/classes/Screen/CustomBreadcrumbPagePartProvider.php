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

use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\Refinery\Factory as RefineryFactory;

class CustomBreadcrumbPagePartProvider implements PagePartProvider
{
    private PagePartProvider $original;
    private RefineryFactory $refinery;
    private ilLogger $logger;

    public function __construct(PagePartProvider $original)
    {
        global $DIC;
        $this->refinery = $DIC->refinery();
        $this->original = $original;
        $this->logger = $DIC->logger()->root();
    }

    public function getTitle(): string
    {
        return $this->original->getTitle();
    }

    public function getDescription(): string
    {
        return $this->original->getDescription();
    }

    private function getRefId(): string|null
    {
        if(isset($_GET["ref_id"])) {
            return (string) $_GET["ref_id"];
        }
        if(isset($_SESSION["lti_context_ids"]) && is_array($_SESSION["lti_context_ids"]) && count($_SESSION["lti_context_ids"]) > 0) {
            return (string) $_SESSION["lti_context_ids"][0];
        }
        return null;
    }

    public function getBreadCrumbs(): ?\ILIAS\UI\Component\Breadcrumbs\Breadcrumbs
    {
        global $DIC;
        $breadcrumbs = $this->original->getBreadCrumbs();
        if ($breadcrumbs === null) {
            return null;
        }

        $ref_id = $this->getRefId();
        if (!isset($ref_id)) {
            return $breadcrumbs;
        }

        $goto_crumbs = [];
        $non_goto_crumbs = [];

        foreach ($breadcrumbs->getItems() as $crumb) {
            $action = (string) $crumb->getAction();
            if (method_exists($crumb, 'getAction') && str_contains($action, 'goto.php')) {
                if (str_contains($action, (string) $ref_id) && !str_contains($action, 'root')) {
                    $goto_crumbs[] = $crumb;
                }
            } else {
                $non_goto_crumbs[] = $crumb;
            }
        }
        $last_goto = array_slice($goto_crumbs, -1);

        $final_crumbs = array_merge($last_goto, $non_goto_crumbs);

        return $DIC->ui()->factory()->breadcrumbs($final_crumbs);

    }

    public function getMeta(): array
    {
        return $this->original->getMeta();
    }

    public function getActions(): array
    {
        return $this->original->getActions();
    }

    public function getContent(): ?\ILIAS\UI\Component\Legacy\Legacy
    {
        return $this->original->getContent();
    }

    public function getMetaBar(): ?\ILIAS\UI\Component\MainControls\MetaBar
    {
        return $this->original->getMetaBar();
    }

    public function getMainBar(): ?\ILIAS\UI\Component\MainControls\MainBar
    {
        return $this->original->getMainBar();
    }

    public function getLogo(): ?\ILIAS\UI\Component\Image\Image
    {
        return $this->original->getLogo();
    }

    public function getResponsiveLogo(): ?\ILIAS\UI\Component\Image\Image
    {
        return $this->original->getResponsiveLogo();
    }

    public function getFaviconPath(): string
    {
        return $this->original->getFaviconPath();
    }

    public function getSystemInfos(): array
    {
        return $this->original->getSystemInfos();
    }

    public function getFooter(): ?\ILIAS\UI\Component\MainControls\Footer
    {
        return $this->original->getFooter();
    }

    public function getShortTitle(): string
    {
        return $this->original->getShortTitle();
    }

    public function getViewTitle(): string
    {
        return $this->original->getViewTitle();
    }

    public function getToastContainer(): ?\ILIAS\UI\Component\Toast\Container
    {
        return $this->original->getToastContainer();
    }
}
