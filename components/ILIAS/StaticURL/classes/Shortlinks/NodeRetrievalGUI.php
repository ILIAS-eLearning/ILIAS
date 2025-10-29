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

use ILIAS\UI\Factory;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Renderer;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\UI\Component\Input\Field\Node\NodeRetrieval as UINodeRetrieval;
use ILIAS\UI\URLBuilder;
use ILIAS\StaticURL\Shortlinks\UI\NodeRetrieval;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class NodeRetrievalGUI implements \ilCtrlBaseClassInterface
{
    protected const string PARENT_NODE_ID_PARAMETER = 'parent_node_id';
    protected const int MAX_BRANCH_NODE_DEPTH = 2; // @todo: declare realistic depth

    protected UINodeRetrieval $node_retrieval;
    protected URLBuilderToken $async_node_id_parameter;
    protected Factory $ui_factory;
    protected Renderer $renderer;
    protected RequestWrapper $get_request;
    protected GlobalHttpState $http;
    protected \ILIAS\Refinery\Factory $refinery;
    protected \ilObjMainMenuAccess $access;
    protected \ilCtrlInterface $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->ui_factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->get_request = $DIC->http()->wrapper()->query();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ctrl = $DIC->ctrl();

        // keep those goddamn parameters alive...
        $this->ctrl->saveParameterByClass(self::class, 'ref_id');

        $data_factory = new \ILIAS\Data\Factory();
        $async_node_url_builder = new URLBuilder(
            $data_factory->uri(
                ILIAS_HTTP_PATH . '/' .
                $this->ctrl->getLinkTargetByClass([self::class], null, null, true)
            ),
        );
        [$async_node_url_builder, $this->async_node_id_parameter] = $async_node_url_builder->acquireParameter(
            explode('\\', __NAMESPACE__),
            self::PARENT_NODE_ID_PARAMETER,
        );

        /** @var \ilObjectDefinition $object_definition */
        $object_definition = $DIC['objDefinition'];

        $branch_node_types = $object_definition->getExplorerContainerTypes();
        $leaf_node_types = array_diff($object_definition->getAllRepositoryTypes(false), $branch_node_types);

        $this->node_retrieval = new NodeRetrieval(
            $DIC->language(),
            $data_factory,
            $this->async_node_id_parameter,
            $async_node_url_builder,
            $DIC->access(),
            $DIC->repositoryTree(),
            $branch_node_types,
            $leaf_node_types,
            (int) ($DIC->settings()->get('rep_tree_limit_number') ?? self::MAX_BRANCH_NODE_DEPTH),
            // ensures the root node is included as well.
            $DIC->repositoryTree()->getParentId(
                $DIC->repositoryTree()->getRootId(),
            ),
        );
    }

    public function executeCommand(): void
    {
        if (!$this->ctrl->isAsynch()) {
            throw new \RuntimeException(self::class . ' must be called asynchronously.');
        }
        if ($this->ctrl->getNextClass($this)) {
            throw new \LogicException(self::class . ' must be the only command class.');
        }

        if (!$this->get_request->has($this->async_node_id_parameter->getName())) {
            $this->sendHtmlResponse('');
            return;
        }
        $parent_node_id = (int) $this->get_request->retrieve(
            $this->async_node_id_parameter->getName(),
            $this->refinery->kindlyTo()->int(),
        );
        $this->renderAsyncNodeChildren($parent_node_id);
    }

    public function getNodeRetrieval(): UINodeRetrieval
    {
        return $this->node_retrieval;
    }

    protected function renderAsyncNodeChildren(int $parent_node_id): void
    {
        $child_node_iterator = $this->node_retrieval->getNodes(
            $this->ui_factory->input()->field()->node(),
            $this->ui_factory->symbol()->icon(),
            (string) $parent_node_id,
        );

        $html = '';
        foreach ($child_node_iterator as $node) {
            $html .= $this->renderer->renderAsync($node);
        }
        $this->sendHtmlResponse($html);
    }

    protected function sendHtmlResponse(string $html): void
    {
        $this->http->saveResponse(
            $this->http->response()
                       ->withHeader('Content-Type', 'text/html; charset=utf-8')
                       ->withBody(Streams::ofString($html))
        );
        $this->http->sendResponse();
        $this->http->close();
    }

}
