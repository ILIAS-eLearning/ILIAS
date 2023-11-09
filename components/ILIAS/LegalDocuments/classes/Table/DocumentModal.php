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

namespace ILIAS\LegalDocuments\Table;

use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\UI\Component\Component;
use ILIAS\DI\UIServices;
use Closure;

class DocumentModal
{
    /**
     * @param Closure(CriterionContent): Component $content_as_component
     */
    public function __construct(
        private readonly UIServices $ui,
        private readonly Closure $content_as_component
    ) {
    }

    /**
     * @return list<Component>
     */
    public function create(DocumentContent $content): array
    {
        $modal = $this->ui->factory()->modal()->lightbox([
            $this->ui->factory()->modal()->lightboxTextPage(
                $this->ui->renderer()->render(($this->content_as_component)($content)),
                $content->title()
            )
        ]);

        $link = $this->ui->factory()->button()->shy($content->title(), '')->withOnClick(
            $modal->getShowSignal()
        );

        return [$link, $modal];
    }
}
