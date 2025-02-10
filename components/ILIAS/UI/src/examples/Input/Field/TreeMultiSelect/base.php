<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\TreeMultiSelect;

use ILIAS\UI\Component\Input\Field\Node\NodeRetrieval;
use ILIAS\UI\Component\Input\Field\Node\Factory as NodeFactory;
use ILIAS\UI\Component\Input\Field\Node\Node;
use ILIAS\UI\Component\Symbol\Icon\Factory as IconFactory;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\URI;
use ILIAS\Filesystem\Stream\Streams;

/**
 * ---
 * description: >
 *   The example shows how to create and render a Tree Multi Select Field and attach it to a
 *   Form. This example also shows data processing.
 *
 * expected output: >
 *   ILIAS shows the Tree Multi Select Field inside a Standard Form. Its label and byline are
 *   correctly rendered next to and underneath the actual input, which is displayed as a Shy
 *   Button that states "Select". Clicking the label or the Shy Button will open a Modal dialog.
 *   The Modal title is the same as the Fields label inside the Form. The Modal footer features
 *   one "Close" Standard Button and one (initially) disabled "Select" Primary Button. Inside
 *   the Modal appears a two-column Drilldown Menu, which lists different kinds of Nodes. Nodes
 *   titled "branch <X>" feature a Glyph, indicating that it can be expanded/opened. Clicking
 *   these Nodes will engage a different Drilldown Menu level. Nodes titled "leaf <X>" cannot be
 *   expanded/openend and do not feature a Glyph. Nodes titled "async branch <X>", similar to
 *   "branch <X>" Nodes, can be expanded/opened and feature a Glyph and are loaded asynchronously.
 *   All kinds of Nodes feature an additional Bulky Button which can be used to select/choose a
 *   Node as value. Clicking this Button will enable the Modals "Select" Button, and, if a
 *   "branch <X>" or "async branch <X>" Node is selected, all of their child-Nodes Bulky Buttons
 *   are disabled. It is possible to select multiple Nodes, as long as they are not nested in one
 *   another. Clicking the same Bulky Button to unselect a Node again will reverse these two
 *   actions. If a Node is selected/choosen and the Modals "Select" Button is clicked, the Modal
 *   is closed and the Node-name(s) will appear above the Fields "Select" Shy Button. Next to this
 *   name a Glyph is visible. Clicking this Glyph will remove the selected Node(s) from above the
 *   Shy Button and will reverse the actions performed in the Modal, like when unselecting a Node
 *   inside the Modal again. If a Node is selected and the Form is submitted, the page is reloaded
 *   and the Node-id (<X>) will appear inside the output printed above the Form. The Field will
 *   display a "dummy leaf node <X>" now.
 * ---
 */
function base(): string
{
    global $DIC;

    $http = $DIC->http();
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $get_request = $http->wrapper()->query();
    $data_factory = new \ILIAS\Data\Factory();
    $refinery_factory = new \ILIAS\Refinery\Factory($data_factory, $DIC->language());

    $example_uri = $data_factory->uri((string) $http->request()->getUri());
    $base_url_builder = new URLBuilder($example_uri);
    [$node_id_url_builder, $node_id_parameter] = $base_url_builder->acquireParameter(explode('\\', __NAMESPACE__), "node_id");
    [$process_form_url_builder ,$process_form_parameter] = $base_url_builder->acquireParameter(explode('\\', __NAMESPACE__), "process");

    $node_retrieval = new TreeMultiSelectExampleNodeRetrieval($node_id_url_builder, $node_id_parameter);

    // simulates an async node rendering endpoint:
    if ($get_request->has($node_id_parameter->getName())) {
        $parent_node_id = $get_request->retrieve(
            $node_id_parameter->getName(),
            $refinery_factory->kindlyTo()->string(),
        );

        $node_generator = $node_retrieval->getNodes(
            $factory->input()->field()->node(),
            $factory->symbol()->icon(),
            $parent_node_id
        );

        $html = '';
        foreach ($node_generator as $node) {
            $html .= $renderer->renderAsync($node);
        }

        $http->saveResponse(
            $http->response()
                 ->withHeader('Content-Type', 'text/html; charset=utf-8')
                 ->withBody(Streams::ofString($html))
        );
        $http->sendResponse();
        $http->close();
    }

    $input = $factory->input()->field()->treeMultiSelect(
        $node_retrieval,
        "select multiple nodes",
        "you can open the select input by clicking the button above.",
    );

    $form = $factory->input()->container()->form()->standard(
        (string) $process_form_url_builder->withParameter($process_form_parameter, '1')->buildURI(),
        [$input]
    );

    // simulates a form processing endpoint:
    if ($get_request->has($process_form_parameter->getName())) {
        $form = $form->withRequest($http->request());
        $data = $form->getData();
    } else {
        $data = 'No submitted data yet.';
    }

    return '<pre>' . print_r($data, true) . '</pre>' . $renderer->render($form);
}

/** @noinspection AutoloadingIssuesInspection */
class TreeMultiSelectExampleNodeRetrieval implements NodeRetrieval
{
    public function __construct(
        protected URLBuilder $builder,
        protected URLBuilderToken $node_id_parameter,
    ) {
    }

    public function getNodes(NodeFactory $node_factory, IconFactory $icon_factory, ?string $parent_id = null): \Generator
    {
        if (null !== $parent_id) {
            yield $this->getExampleNodeChildren($node_factory, $parent_id);
            return;
        }

        yield $node_factory->branch('1', 'branch 1', null, ...$this->getExampleNodeChildren($node_factory, '1'));
        yield $node_factory->branch('2', 'branch 2', null, ...$this->getExampleNodeChildren($node_factory, '2'));
        yield $node_factory->leaf('3', 'leaf 3');
    }

    public function getNodesAsLeaf(
        NodeFactory $node_factory,
        IconFactory $icon_factory,
        array $node_ids,
    ): \Generator {
        foreach ($node_ids as $node_id) {
            yield $node_factory->leaf($node_id, "dummy leaf node $node_id");
        }
    }

    protected function getExampleNodeChildren(NodeFactory $node_factory, string|int $parent_id): array
    {
        return [
            $node_factory->branch("$parent_id.1", "branch $parent_id.1", null,
                $node_factory->leaf("$parent_id.1.1", "leaf $parent_id.1.1"),
                $node_factory->leaf("$parent_id.1.2", "leaf $parent_id.1.2"),
                $node_factory->leaf("$parent_id.1.3", "leaf $parent_id.1.3"),
            ),
            $node_factory->branch("$parent_id.2", "branch $parent_id.2", null,
                $node_factory->leaf("$parent_id.2.1", "leaf $parent_id.2.1"),
                $node_factory->leaf("$parent_id.2.2", "leaf $parent_id.2.2"),
                $node_factory->leaf("$parent_id.2.3", "leaf $parent_id.2.3"),
            ),
            $node_factory->async($this->getAsyncNodeRenderUrl("$parent_id.3"),"$parent_id.3", "async branch $parent_id.3"),
            $node_factory->leaf("$parent_id.4", "leaf $parent_id.4"),
        ];
    }

    protected function getAsyncNodeRenderUrl(int|string $node_id): URI
    {
        return $this->builder->withParameter($this->node_id_parameter, (string) $node_id)->buildURI();
    }
}
