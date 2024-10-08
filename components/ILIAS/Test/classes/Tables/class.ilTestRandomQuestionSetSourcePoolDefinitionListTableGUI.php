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

use GuzzleHttp\Psr7\ServerRequest;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\URI;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Test\Questions\QuestionPoolLinkedTitleBuilder;
use ILIAS\Test\Questions\RandomQuestionSetSourcePoolDefinitionListTable;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\Component\Table\Ordering as OrderingTable;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilTestRandomQuestionSetSourcePoolDefinitionList as ilPoolDefinitionList;

/**
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package	Modules/Test
 */
class ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI
{
    use QuestionPoolLinkedTitleBuilder;

    protected DataFactory $data_factory;
    protected RandomQuestionSetSourcePoolDefinitionListTable $table;
    protected bool $editable = false;

    /**
     * @param ilAccess $access
     * @param ilCtrlInterface $ctrl
     * @param ilLanguage $lng
     * @param UIFactory $ui_factory
     * @param UIRenderer $ui_renderer
     * @param GlobalHttpState $http
     * @param ilTestQuestionFilterLabelTranslater $translater
     */
    public function __construct(
        protected ilAccess $access,
        protected readonly ilCtrlInterface $ctrl,
        protected readonly ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected readonly GlobalHttpState $http,
        ilTestQuestionFilterLabelTranslater $translater
    ) {
        $this->data_factory = new DataFactory();
        $this->table = new RandomQuestionSetSourcePoolDefinitionListTable(
            $this->lng,
            $this->ui_factory,
            $this->data_factory,
            $translater,
            fn(int $ref_id, string $source_pool_label) => $this->createShowPoolLink($ref_id, $source_pool_label)
        );
    }

    /**
     * @param ilPoolDefinitionList $source_pool_definition_list
     */
    public function setData(ilPoolDefinitionList $source_pool_definition_list): void
    {
        $this->table->setData($source_pool_definition_list);
    }

    /**
     * @param bool $editable
     */
    public function setEditable(bool $editable): void
    {
        $this->table->setEditable($this->editable = $editable);
    }

    /**
     * @param bool $show_amount
     */
    public function setShowAmount(bool $show_amount): void
    {
        $this->table->setShowAmount($show_amount);
    }

    /**
     * @param bool $show_filter
     */
    public function setShowMappedTaxonomyFilter(bool $show_filter): void
    {
        $this->table->setShowMappedTaxonomyFilter($show_filter);
    }

    /**
     * @return string
     * @throws ilCtrlException
     */
    public function getHTML(): string
    {
        return $this->ui_renderer->render($this->buildTable());
    }

    /**
     * @param int $ref_id
     * @param string $source_pool_label
     * @return Link
     */
    public function createShowPoolLink(int $ref_id, string $source_pool_label): Link
    {
        $available = $this->getFirstReferenceWithCurrentUserAccess(
            $this->access,
            true,
            $ref_id,
            \ilObject::_getAllReferences($ref_id)
        ) !== null;
        return $this->getLinkedTitle($this->ctrl, $this->ui_factory, $ref_id, $source_pool_label, \ilObjQuestionPoolGUI::class)
            ->withDisabled(!$available);
    }

    /**
     * @return OrderingTable
     * @throws ilCtrlException
     */
    protected function buildTable(): OrderingTable
    {
        $target = $this->buildTargetURI(ilTestRandomQuestionSetConfigGUI::CMD_SAVE_SRC_POOL_DEF_LIST);
        $title = $this->lng->txt('tst_src_quest_pool_def_list_table');
        return $this->ui_factory->table()
            ->ordering($title, $this->table->getColumns(), $this->table, $target)
            ->withRequest($this->http->request())
            ->withActions($this->getActions())
            ->withOrderingDisabled(!$this->editable)
            ->withId('src_pool_def_list');
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     * @throws ilCtrlException
     */
    protected function getActions(): array
    {
        return [
          'delete' => $this->ui_factory->table()->action()->standard(
              $this->lng->txt('delete'),
              ... $this->getActionURI(ilTestRandomQuestionSetConfigGUI::CMD_DELETE_MULTI_SRC_POOL_DEFS, true)
          ),
           'edit' => $this->ui_factory->table()->action()->single(
               $this->lng->txt('edit'),
               ... $this->getActionURI(ilTestRandomQuestionSetConfigGUI::CMD_SHOW_EDIT_SRC_POOL_DEF_FORM)
           )
        ];
    }

    /**
     * @param string $cmd
     * @param bool $multi
     * @return array{URLBuilder, URLBuilderToken}
     * @throws ilCtrlException
     */
    protected function getActionURI(string $cmd, bool $multi = false): array
    {
        $builder = new URLBuilder($this->buildTargetURI($cmd));
        return $builder->acquireParameters(['src_pool_def'], $multi ? 'ids' : 'id');
    }

    /**
     * @param string $cmd
     * @return URI
     * @throws ilCtrlException
     */
    protected function buildTargetURI(string $cmd): URI
    {
        $target = $this->ctrl->getLinkTargetByClass(ilTestRandomQuestionSetConfigGUI::class, $cmd);
        $path = parse_url($target, PHP_URL_PATH);
        $query = parse_url($target, PHP_URL_QUERY);
        return $this->data_factory->uri((string) ServerRequest::getUriFromGlobals()->withPath($path)->withQuery($query));
    }

    /**
     * @param ilPoolDefinitionList $source_pool_definition_list
     * @param $request
     * @throws ilCtrlException
     */
    public function applySubmit(ilPoolDefinitionList $source_pool_definition_list, $request): void
    {
        $quest_pos = array_flip($this->buildTable()->getData());
        $quest_amounts = $request->raw('quest_amount');
        $show_amount = $this->table->showAmount();

        foreach ($source_pool_definition_list as $source_pool_definition) {
            $source_pool_definition->setSequencePosition($quest_pos[$source_pool_definition->getId()] ?? 0);

            $amount = (int) $quest_amounts[$source_pool_definition->getId()] ?? 0;
            $source_pool_definition->setQuestionAmount($show_amount ? $amount : null);
        }
    }
}
