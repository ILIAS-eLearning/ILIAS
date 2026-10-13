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

namespace ILIAS\LearningSequence\LearningMap;

/**
 * Renders Learning Map data as an interactive HTML graph.
 */
class LSOLearningMapRenderer
{
    public const TEMPLATE = 'tpl.lso_learning_map.html';
    public const COMPONENT = 'components/ILIAS/LearningSequence';
    public const JS = 'assets/js/lso_learning_map.js';
    public const CSS = 'assets/css/lso_learning_map.css';

    public const ORIENTATION_VERTICAL = 'vertical';
    public const ORIENTATION_HORIZONTAL = 'horizontal';

    /**
     * Glyphs shown in the object boxes instead of the former state badges.
     * Change the values here to switch a glyph; the keys are used by the map
     * script and by the legend.
     *
     * @var array<string, string>
     */
    public const STATE_GLYPHS = [
        // 'current' => \ILIAS\UI\Component\Symbol\Glyph\Glyph::NEXT,
        'done' => \ILIAS\UI\Component\Symbol\Glyph\Glyph::CHECKED,
        // 'open' => \ILIAS\UI\Component\Symbol\Glyph\Glyph::UNCHECKED,
        // 'blocked' => \ILIAS\UI\Component\Symbol\Glyph\Glyph::CLOSE
    ];

    private const NODE_WIDTH = 190;
    private const NODE_HEIGHT = 86;
    private const H_GAP = 26;
    private const V_GAP = 96;

    /**
     * Provides localized Learning Map labels.
     */
    private \ilLanguage $lng;

    public function __construct(
        private \ILIAS\UI\Factory $ui_factory,
        private \ILIAS\UI\Renderer $ui_renderer,
        private \ilGlobalTemplateInterface $tpl,
        private bool $sequential = false
    ) {
        global $DIC;
        $this->lng = $DIC->language();
    }

    /**
     * @param array{nodes: array<int, array<string, mixed>>, edges: array<int, array<string, mixed>>} $graph
     */
    public function render(array $graph): string
    {
        if ($graph['nodes'] === []) {
            return $this->ui_renderer->render(
                $this->ui_factory->panel()->standard(
                    $this->txt('learning_map'),
                    $this->ui_factory->messageBox()->info($this->getEmptyMessage())
                )
            );
        }

        return $this->ui_renderer->render(
            $this->ui_factory->panel()->standard(
                $this->txt('learning_map'),
                $this->ui_factory->legacy()->content($this->buildMarkup($graph))
            )
        );
    }

    /**
     * @param array{nodes: array<int, array<string, mixed>>, edges: array<int, array<string, mixed>>} $graph
     */
    public function renderWithoutPanel(array $graph): string
    {
        if ($graph['nodes'] === []) {
            return $this->ui_renderer->render(
                $this->ui_factory->messageBox()->info($this->getEmptyMessage())
            );
        }

        return $this->buildMarkup($graph);
    }

    protected function getEmptyMessage(): string
    {
        return $this->txt('lso_learning_map_empty');
    }

    /**
     * @param array{nodes: array<int, array<string, mixed>>, edges: array<int, array<string, mixed>>} $graph
     */
    protected function buildMarkup(array $graph): string
    {
        $this->tpl->addCss(self::CSS);
        $this->tpl->addJavaScript(self::JS);

        $map_id = 'lso_learning_map_' . substr(md5((string) mt_rand()), 0, 8);

        $template = new \ilTemplate(self::TEMPLATE, true, true, self::COMPONENT);
        $template->setVariable('MAP_ID', $map_id);
        $template->setVariable(
            'MAP_MODIFIER',
            $this->sequential ? ' lso-learning-map--horizontal' : ''
        );
        $template->setVariable('TOOLBAR', $this->renderToolbar($map_id));
        $template->setVariable('VIEWPORT_LABEL', $this->getViewportLabel());
        $template->setVariable('MAP_DATA', $this->getMapDataAsJson($graph));

        foreach ($this->getLegend() as $modifier => $label) {
            $template->setCurrentBlock('legend_item');
            $template->setVariable('LEGEND_MODIFIER', $modifier);
            $template->setVariable('LEGEND_LABEL', $label);
            $template->parseCurrentBlock();
        }

        foreach ($this->getGlyphLegend() as $key => $entry) {
            $template->setCurrentBlock('legend_glyph_item');
            $template->setVariable('LEGEND_GLYPH_KEY', $key);
            $template->setVariable('LEGEND_GLYPH', $entry['glyph']);
            $template->setVariable('LEGEND_GLYPH_LABEL', $entry['label']);
            $template->parseCurrentBlock();
        }

        return $template->get();
    }

    /**
     * @param array{nodes: array<int, array<string, mixed>>, edges: array<int, array<string, mixed>>} $graph
     */
    protected function getMapDataAsJson(array $graph): string
    {
        return json_encode(
            $graph + [
                'orientation' => $this->sequential
                    ? self::ORIENTATION_HORIZONTAL
                    : self::ORIENTATION_VERTICAL,
                'metrics' => [
                    'node_width' => self::NODE_WIDTH,
                    'node_height' => self::NODE_HEIGHT,
                    'h_gap' => self::H_GAP,
                    'v_gap' => self::V_GAP,
                ],
                'labels' => $this->getNodeLabels() + $this->getScreenReaderLabels(),
                'glyphs' => $this->getStateGlyphs()
            ],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );
    }

    /**
     * @return array<string, string>
     */
    protected function getNodeLabels(): array
    {
        return [
            'current' => $this->txt('lso_learning_map_current'),
            'done' => $this->txt('lso_learning_map_done'),
            'open' => $this->txt('lso_learning_map_open'),
            'blocked' => $this->txt('lso_learning_map_blocked'),
            'open_object' => $this->txt('lso_learning_map_open_object')
        ];
    }

    /**
     * Rendered glyph markup per state, keyed like self::STATE_GLYPHS.
     *
     * @return array<string, string>
     */
    protected function getStateGlyphs(): array
    {
        $glyphs = [];
        foreach (self::STATE_GLYPHS as $key => $type) {
            $glyphs[$key] = $this->ui_renderer->render(
                $this->ui_factory->symbol()->glyph()->$type()
                    ->withLabel($this->getStateGlyphLabel($key))
            );
        }

        return $glyphs;
    }

    /**
     * @return array<string, array{glyph: string, label: string}>
     */
    protected function getGlyphLegend(): array
    {
        $legend = [];
        foreach ($this->getStateGlyphs() as $key => $glyph) {
            if ($this->sequential && $key === 'current') {
                continue;
            }
            $legend[$key] = [
                'glyph' => $glyph,
                'label' => $this->getStateGlyphLabel($key)
            ];
        }

        return $legend;
    }

    protected function getStateGlyphLabel(string $key): string
    {
        return $this->txt('lso_learning_map_' . $key);
    }

    /**
     * @return array<string, string>
     */
    protected function getScreenReaderLabels(): array
    {
        return [
            'sr_summary' => $this->txt('lso_learning_map_sr_summary'),
            'sr_summary_current' => $this->txt('lso_learning_map_sr_summary_current'),
            'sr_summary_start' => $this->txt('lso_learning_map_sr_summary_start'),
            'sr_summary_end' => $this->txt('lso_learning_map_sr_summary_end'),
            'sr_step' => $this->txt('lso_learning_map_sr_step'),
            'sr_state_done' => $this->txt('lso_learning_map_sr_state_done'),
            'sr_state_open' => $this->txt('lso_learning_map_sr_state_open'),
            'sr_state_blocked' => $this->txt('lso_learning_map_sr_state_blocked'),
            'sr_current' => $this->txt('lso_learning_map_sr_current'),
            'sr_start' => $this->txt('lso_learning_map_sr_start'),
            'sr_end' => $this->txt('lso_learning_map_sr_end'),
            'sr_leads_to' => $this->txt('lso_learning_map_sr_leads_to'),
            'sr_leads_to_none' => $this->txt('lso_learning_map_sr_leads_to_none'),
            'sr_blocked_way' => $this->txt('lso_learning_map_sr_blocked_way'),
            'sr_fitted' => $this->txt('lso_learning_map_sr_fitted'),
            'sr_zoom' => $this->txt('lso_learning_map_sr_zoom'),
            'sr_at_current' => $this->txt('lso_learning_map_sr_at_current'),
            'sr_no_current' => $this->txt('lso_learning_map_sr_no_current')
        ];
    }

    protected function getViewportLabel(): string
    {
        return $this->txt('lso_learning_map_viewport');
    }

    /**
     * @return array<string, string>
     */
    protected function getLegend(): array
    {
        $legend = [
            //'open' => $this->txt('lso_learning_map_open'),
            //'blocked' => $this->txt('lso_learning_map_blocked'),
            //'path' => $this->txt('lso_learning_map_path'),
            'node_open' => $this->txt('lso_learning_map_node_open'),
            'node_blocked' => $this->txt('lso_learning_map_node_blocked'),
            //'done' => $this->txt('lso_learning_map_done'),
            'current' => $this->txt('lso_learning_map_current')
        ];

        if ($this->sequential) {

            unset($legend['path'], $legend['current']);
        }

        return $legend;
    }
    protected function renderToolbar(string $map_id): string
    {
        $actions = [
            $this->txt('lso_learning_map_zoom_out_symbol') => [$this->txt('lso_learning_map_zoom_out'), 'zoomBy(-0.15)'],
            $this->txt('lso_learning_map_zoom_in_symbol') => [$this->txt('lso_learning_map_zoom_in'), 'zoomBy(0.15)'],
            $this->txt('lso_learning_map_reset_zoom_label') => [$this->txt('lso_learning_map_reset_zoom'), 'resetZoom()'],
            $this->txt('lso_learning_map_fit') => [$this->txt('lso_learning_map_fit'), 'fit()'],
            $this->txt('lso_learning_map_focus_current') => [$this->txt('lso_learning_map_focus_current'), 'focusCurrent()']
        ];

        if ($this->sequential) {
            unset(
                $actions[$this->txt('lso_learning_map_focus_current')],
                $actions[$this->txt('lso_learning_map_zoom_out_symbol')],
                $actions[$this->txt('lso_learning_map_zoom_in_symbol')],
                $actions[$this->txt('lso_learning_map_reset_zoom_label')]
            );
        }

        $buttons = [];
        foreach ($actions as $label => [$aria_label, $call]) {
            $buttons[] = $this->ui_factory->button()->standard((string) $label, '#')
                ->withAriaLabel($aria_label)
                ->withAdditionalOnLoadCode(
                    fn($id) => $this->bindToApi((string) $id, $map_id, $call)
                );
        }

        return $this->ui_renderer->render($buttons);
    }

    protected function bindToApi(string $button_id, string $map_id, string $call): string
    {
        // The button is not necessarily on the page when this runs: an async
        // re-render elsewhere (e.g. the rating box of the kiosk player) ships
        // the whole accumulated onload buffer with its snippet, and a hard
        // reference would throw there and kill every handler after it.
        return 'var el = document.getElementById("' . $button_id . '");'
            . 'if (el) { el.addEventListener("click", function (event) {'
            . 'event.preventDefault();'
            . 'var map = window.il && window.il.LSO && window.il.LSO.LearningMap'
            . ' ? window.il.LSO.LearningMap.get("' . $map_id . '") : null;'
            . 'if (map) { map.' . $call . '; }'
            . '}); }';
    }

    private function txt(string $key): string
    {
        return $this->lng->txt($key);
    }

    /**
     * @param array{nodes?: array<int, array<string, mixed>>, start_obj_id?: int, end_obj_id?: int} $map
     * @return array{nodes: array<int, array<string, mixed>>, edges: array<int, array<string, mixed>>}
     */
    public function fromMapData(array $map): array
    {
        $map_nodes = $map['nodes'] ?? [];
        $start_obj_id = (int) ($map['start_obj_id'] ?? 0);
        $end_obj_id = (int) ($map['end_obj_id'] ?? 0);

        $known = [];
        foreach ($map_nodes as $node) {
            $known[(int) $node['obj_id']] = $node;
        }

        $nodes = [];
        $edges = [];
        foreach ($map_nodes as $node) {
            $obj_id = (int) $node['obj_id'];
            $situation = (string) ($node['situation'] ?? '');
            $can_access = (bool) ($node['can_access'] ?? false);
            $state = 'blocked';
            if ($can_access && !empty($node['has_completed'])) {
                $state = 'done';
            } elseif ($can_access) {
                $state = 'open';
            }

            $terminal = null;
            if ($obj_id === $start_obj_id || $situation === 'start') {
                $terminal = 'start';
            } elseif ($obj_id === $end_obj_id || $situation === 'end') {
                $terminal = 'end';
            }

            $nodes[] = [
                'id' => (string) $obj_id,
                'title' => (string) ($node['title'] ?? ''),
                'description' => (string) ($node['description'] ?? ''),
                'icon' => (string) ($node['icon'] ?? ''),
                'href' => $node['player_link'] ?? null,
                'state' => $state,
                'current' => (bool) ($node['is_current'] ?? false),
                'terminal' => $terminal,
            ];
            $passable_successors = array_map('intval', (array) ($node['passable_successors'] ?? []));

            foreach ($node['successors'] ?? [] as $successor_obj_id) {
                $successor_obj_id = (int) $successor_obj_id;
                if (!isset($known[$successor_obj_id])) {
                    continue;
                }
                $successor = $known[$successor_obj_id];
                $edges[] = [
                    'from' => (string) $obj_id,
                    'to' => (string) $successor_obj_id,
                    'passable' => in_array($successor_obj_id, $passable_successors, true),
                    'on_path' => !empty($node['is_on_walked_path'])
                        && !empty($successor['is_on_walked_path']),
                ];
            }
        }

        return ['nodes' => $nodes, 'edges' => $edges];
    }
}
