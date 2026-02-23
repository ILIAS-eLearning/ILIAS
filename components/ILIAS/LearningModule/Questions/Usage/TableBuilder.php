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

namespace ILIAS\LearningModule\Question\Usage;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ilLanguage;
use ilCtrl;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\TestQuestionPool\Questions\PublicInterface as QuestionInfo;

class TableBuilder extends CommonTableBuilder
{
    protected ilCtrl $ctrl;
    protected QuestionInfo $question_info;
    protected ilLanguage $lng;
    protected UIFactory $ui_factory;

    public function __construct(
        object $parent_gui,
        string $parent_cmd,
        protected int $obj_id
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->question_info = $DIC->testQuestion();
        $this->lng = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();

        $this->lng->loadLanguageModule('cont');

        parent::__construct($parent_gui, $parent_cmd, true);
    }

    protected function getId(): string
    {
        return 'question_usage';
    }

    protected function getTitle(): string
    {
        return $this->lng->txt('cont_question_page_usage');
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return new Retrieval(
            $this->obj_id,
            $this->ctrl,
            $this->question_info
        );
    }

    protected function transformRow(array $data_row): array
    {
        $data = [
            'id' => $data_row['id'],
            'title' => $data_row['title'] ?? '',
            'page' => $this->ui_factory->link()->standard(
                $data_row['page_title'] ?? '',
                $data_row['page_link'] ?? ''
            )
        ];
        return $data;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        return $table
            ->textColumn('title', $this->lng->txt('cont_question'))
            ->linkColumn('page', $this->lng->txt('cont_page_usage'));
    }
}
