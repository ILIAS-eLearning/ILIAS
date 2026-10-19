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

namespace ILIAS\Scripts\Privacy\Documentation;

/**
 * Orchestrates parser, classifier, and renderer, and writes or checks the
 * report file per component.
 *
 * Input are the resolve() calls reported by PHPStan. The report is written
 * to its own file next to the handwritten PRIVACY.md; the generator never
 * touches PRIVACY.md itself.
 */
readonly class PrivacyDocGenerator
{
    public const string TARGET_FILENAME = 'PRIVACY_DATA.md';
    private const string HANDWRITTEN_FILENAME = 'PRIVACY.md';

    public function __construct(
        private string $phpstan_output_file,
        private string $components_base_dir,
        private bool $dry_run = false,
        private ?string $filter_component = null,
        private bool $complete_analysis = true,
    ) {
    }

    /**
     * Writes the report of every component that resolves privacy data
     * types, and removes the reports of other
     * components (unless this is a dry run).
     *
     * @return array<string, string> component => report file path (written or removed)
     */
    public function run(): array
    {
        $rendered = $this->render();
        $written = [];
        foreach ($rendered as $component => [$path, $content]) {
            if (!$this->dry_run && file_put_contents($path, $content) === false) {
                throw new \RuntimeException("Cannot write {$path}.");
            }
            $written[$component] = $path;
        }
        foreach ($this->orphans($rendered) as $component => $path) {
            if (!$this->dry_run) {
                unlink($path);
            }
            $written[$component] = $path;
        }
        ksort($written);
        return $written;
    }

    /**
     * Writes nothing and returns the reports that are missing or differ
     * from what the code yields, e.g. to fail a CI job.
     *
     * @return array<string, string> component => report file path
     */
    public function check(): array
    {
        $rendered = $this->render();
        $stale = $this->orphans($rendered);
        foreach ($rendered as $component => [$path, $content]) {
            if (!is_file($path) || file_get_contents($path) !== $content) {
                $stale[$component] = $path;
            }
        }
        ksort($stale);
        return $stale;
    }

    /**
     * Existing reports of components the analysis no longer yields entries for.
     * Only known for an analysis of all components; a partial one would make
     * every other report look orphaned.
     *
     * @param array<string, array{0: string, 1: string}> $rendered
     * @return array<string, string> component => report file path
     */
    private function orphans(array $rendered): array
    {
        $orphans = [];
        if (!$this->complete_analysis) {
            return $orphans;
        }
        // scandir() instead of glob(): works with stream wrappers as well
        foreach (scandir($this->components_base_dir) ?: [] as $component) {
            $path = "{$this->components_base_dir}/{$component}/" . self::TARGET_FILENAME;
            if ($component === '.' || $component === '..' || !is_file($path)) {
                continue;
            }
            if ($this->filter_component !== null && $component !== $this->filter_component) {
                continue;
            }
            if (!isset($rendered[$component])) {
                $orphans[$component] = $path;
            }
        }
        return $orphans;
    }

    /**
     * @return array<string, array{0: string, 1: string}> component => [path, content]
     */
    private function render(): array
    {
        $parser = new AnalysisResultParser();
        $classifier = new TechnicalRequirementClassifier();
        $renderer = new MarkdownRenderer();

        /** @var array<string, ComponentReport> $reports */
        $reports = [];
        foreach ($parser->parse($this->phpstan_output_file) as $raw) {
            $component = $raw['component'];
            if ($component === 'Unknown') {
                // code outside components/ILIAS, e.g. plugins, has no PRIVACY.md here
                continue;
            }
            if ($this->filter_component !== null && $component !== $this->filter_component) {
                continue;
            }
            $reports[$component] ??= new ComponentReport();
            $reports[$component]->add($classifier->classify($raw));
        }
        ksort($reports);

        $has_privacy_md = fn(string $component): bool =>
            is_file("{$this->components_base_dir}/{$component}/" . self::HANDWRITTEN_FILENAME);

        $rendered = [];
        foreach ($reports as $component => $report) {
            $rendered[$component] = [
                "{$this->components_base_dir}/{$component}/" . self::TARGET_FILENAME,
                $renderer->render($component, $report, $has_privacy_md),
            ];
        }
        return $rendered;
    }
}
