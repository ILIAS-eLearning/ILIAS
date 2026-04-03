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

namespace ILIAS\Questions\AnswerForm\Capabilities\SuggestedLearningContent;

use ILIAS\Questions\Presentation\Definitions\Actor;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Data\URI;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Component\Input\Field\Node\NodeRetrieval as NodeRetrievalInterface;
use ILIAS\UI\Component\Input\Field\Node\Factory as NodeFactory;
use ILIAS\UI\Component\Symbol\Icon\Factory as IconFactory;
use ILIAS\UI\Component\Symbol\Icon\Standard as StandardIcon;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class NodeRetrieval implements NodeRetrievalInterface, Actor
{
    private const string SUB_ACTION_RETRIEVE_NODES = 'rn';

    private const array PARAMETER_NAMESPACE = ['q', 'nr'];
    private const string PARAMETER_ID_STRING_NODE = 'n';

    private const array CONTAINER_CONTENT_TYPES = [
        'cat',
        'grp',
        'crs',
        'fold'
    ];

    private readonly URLBuilder $url_builder;
    private readonly URLBuilderToken $node_parameter_token;

    /**
     * @var list<string>
     */
    private readonly array $requested_types;

    public function __construct(
        private readonly \ilRbacSystem $rbac_system,
        private readonly \ilTree $tree,
        private readonly Environment $environment,
        private readonly string $requested_type
    ) {
        [$this->url_builder, $this->node_parameter_token] = $environment
            ->withSubActionParameter(self::SUB_ACTION_RETRIEVE_NODES)
            ->getUrlBuilder()
            ->acquireParameter(
                self::PARAMETER_NAMESPACE,
                self::PARAMETER_ID_STRING_NODE
            );

        $this->requested_types = array_merge(
            self::CONTAINER_CONTENT_TYPES,
            [$requested_type]
        );
    }

    #[\Override]
    public function getNodes(
        NodeFactory $node_factory,
        IconFactory $icon_factory,
        array $sync_node_id_whitelist = [],
        ?string $parent_id = null
    ): \Generator {
        if ($parent_id === null) {
            $parent_id = ROOT_FOLDER_ID;
        }

        yield from $this->buildFilteredNodes(
            $node_factory,
            $icon_factory,
            $this->tree->getChildsByTypeFilter(
                $parent_id,
                $this->requested_types
            )
        );
    }

    #[\Override]
    public function getNodesAsLeaf(
        NodeFactory $node_factory,
        IconFactory $icon_factory,
        array $node_ids,
    ): \Generator {
        foreach ($node_ids as $node_id) {
            $node = $this->tree->getNodeData((int) $node_id);
            yield $node_factory->leaf(
                explode('.', $node['path'] ?? ''),
                $node['title'] ?? $this->environment->getLanguage()->txt('invalid')
            );
        }
    }

    #[\Override]
    public function can(
        string $sub_action
    ): bool {
        return $sub_action === self::SUB_ACTION_RETRIEVE_NODES
            && $this->environment->getHttpServices()->wrapper()->query()->has(
                $this->node_parameter_token->getName()
            );
    }

    #[\Override]
    public function do(
        string $action
    ): Async {
        $node_id = $this->retrieveNodeIdFromQuery();

        $response = '';
        if ($node_id !== null) {
            $response = iterator_to_array(
                $this->buildFilteredNodes(
                    $this->environment->getUIFactory()->input()->field()->node(),
                    $this->environment->getUIFactory()->symbol()->icon(),
                    $this->tree->getChildsByTypeFilter(
                        $node_id,
                        $this->requested_types
                    )
                )
            );
        }

        return $this->environment->getPresentationFactory()->getAsync($response);
    }

    public function buildValidNodeConstraint(): Constraint
    {
        return $this->environment->getRefinery()->custom()->constraint(
            function (array $v): bool {
                if (!isset($v[0])) {
                    return false;
                }
                $data = $this->tree->getNodeData((int) $v[0]);
                return $data['type'] === $this->requested_type;
            },
            function (\Closure $txt, array $v): string {
                if (!isset($v[0])) {
                    return $txt('required');
                }
                $data = $this->tree->getNodeData((int) $v[0]);
                return sprintf(
                    $txt('invalid_node_selected'),
                    $txt("obj_{$data['type']}")
                );
            }
        );
    }

    private function buildFilteredNodes(
        NodeFactory $node_factory,
        IconFactory $icon_factory,
        array $nodes
    ): \Generator {
        foreach ($nodes as $node) {
            $is_visible = $this->rbac_system->checkAccess('visible', $node['ref_id']);
            $is_container = in_array($node['type'], self::CONTAINER_CONTENT_TYPES);
            if (!$is_container && !$is_visible) {
                continue;
            }

            if (!$is_container) {
                yield $node_factory->leaf(
                    explode('.', $node['path']),
                    $node['title'],
                    $this->getIconForNodeArray(
                        $icon_factory,
                        $node
                    )
                );
                continue;
            }

            $is_readable = $this->rbac_system->checkAccess('read', $node['ref_id']);
            if ($is_visible && $is_readable && $node['parent'] === ROOT_FOLDER_ID) {
                yield $node_factory->branch(
                    explode('.', $node['path']),
                    $node['title'],
                    $this->getIconForNodeArray(
                        $icon_factory,
                        $node
                    ),
                    ...$this->buildFilteredNodes(
                        $node_factory,
                        $icon_factory,
                        $this->tree->getChildsByTypeFilter(
                            $node['ref_id'],
                            $this->requested_types
                        )
                    )
                );
                continue;
            }

            if ($is_visible && $is_readable) {
                yield $node_factory->async(
                    $this->getAsyncNodeRenderUrl($node['ref_id']),
                    explode('.', $node['path']),
                    $node['title'],
                    $this->getIconForNodeArray(
                        $icon_factory,
                        $node
                    )
                );
                continue;
            }

            if ($is_readable) {
                yield from $this->buildFilteredNodes(
                    $node_factory,
                    $icon_factory,
                    $this->tree->getChildsByTypeFilter(
                        $node['ref_id'],
                        $this->requested_types
                    )
                );
            }
        }
    }

    private function getAsyncNodeRenderUrl(
        int $node_id
    ): URI {
        return $this->url_builder->withParameter(
            $this->node_parameter_token,
            (string) $node_id
        )->buildURI();
    }

    private function retrieveNodeIdFromQuery(): ?int
    {
        $refinery = $this->environment->getRefinery();

        return $this->environment
            ->getHttpServices()
            ->wrapper()
            ->query()
            ->retrieve(
                $this->node_parameter_token->getName(),
                $refinery->byTrying([
                    $refinery->kindlyTo()->int(),
                    $refinery->always(null)
                ])
            );
    }

    private function getIconForNodeArray(
        IconFactory $icon_factory,
        array $node
    ): StandardIcon {
        return $icon_factory->standard(
            $node['type'],
            $this->environment->getLanguage()->txt(
                "obj_{$node['type']}"
            )
        );
    }
}
