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

namespace ILIAS\Blog\Navigation\Link;

use ILIAS\Blog\Presentation\PresentationGUI;

/**
 * Standard link builder using ilCtrl for web view.
 */
class PresentationLinkBuilder implements LinkBuilder
{
    protected const string PAR_MONTH = "bmn";
    protected const string PAR_POSTING = "blpg";
    protected const string PAR_KEYWORD = "kwd";
    protected const string PAR_AUTHOR = "ath";

    protected const string VIEW_MAIN = "main";
    protected const string VIEW_POSTING = "posting";


    public function __construct(
        protected \ilCtrl $ctrl
    ) {
    }

    protected function getPathForView(string $view): array
    {
        return match ($view) {
            self::VIEW_MAIN => [
                \ilObjBlogGUI::class,
                PresentationGUI::class
            ],
            self::VIEW_POSTING => [
                \ilObjBlogGUI::class,
                PresentationGUI::class,
                \ilBlogPostingGUI::class
            ],
        };
    }

    protected function getCmdForView(string $view): string
    {
        return match ($view) {
            self::VIEW_MAIN => "preview",
            self::VIEW_POSTING => "previewFullscreen",
        };
    }

    protected function setParameter(
        string $view,
        string $par,
        string $value
    ): void {
        $path = $this->getPathForView($view);
        $class = end($path);
        $this->ctrl->setParameterByClass($class, $par, $value);
    }

    protected function getLinkForView(string $view): string
    {
        return $this->ctrl->getLinkTargetByClass(
            $this->getPathForView($view),
            $this->getCmdForView($view)
        );
    }

    public function forMonth(string $month): string
    {
        $this->setParameter(self::VIEW_MAIN, self::PAR_MONTH, $month);
        $this->setParameter(self::VIEW_MAIN, self::PAR_POSTING, "");
        return $this->getLinkForView(self::VIEW_MAIN);
    }

    public function forPosting(int $posting_id, string $month = ""): string
    {
        $this->setParameter(self::VIEW_POSTING, self::PAR_MONTH, $month);
        $this->setParameter(self::VIEW_POSTING, self::PAR_POSTING, (string) $posting_id);
        return $this->getLinkForView(self::VIEW_POSTING);
    }

    public function forKeyword(string $keyword): string
    {
        $this->setParameter(self::VIEW_MAIN, self::PAR_KEYWORD, urlencode($keyword));
        $this->setParameter(self::VIEW_MAIN, self::PAR_POSTING, "");
        $link = $this->getLinkForView(self::VIEW_MAIN);
        $this->setParameter(self::VIEW_MAIN, self::PAR_KEYWORD, "");
        return $link;
    }

    public function forAuthor(int $user_id): string
    {
        $this->setParameter(self::VIEW_MAIN, self::PAR_AUTHOR, (string) $user_id);
        $this->setParameter(self::VIEW_MAIN, self::PAR_POSTING, "");
        $link = $this->getLinkForView(self::VIEW_MAIN);
        $this->setParameter(self::VIEW_MAIN, self::PAR_AUTHOR, "");
        return $link;
    }

    public function forMainList(): string
    {
        $this->setParameter(self::VIEW_MAIN, self::PAR_MONTH, "");
        $this->setParameter(self::VIEW_MAIN, self::PAR_POSTING, "");
        return $this->getLinkForView(self::VIEW_MAIN);
    }
}
