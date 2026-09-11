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

namespace ILIAS\Data\Privacy\PHPStan\Generator;

/**
 * Orchestrates parser, classifier, and renderer, and writes the report
 * files per component.
 */
final readonly class PrivacyDocGenerator
{
    public const string DEFAULT_TARGET_FILENAME = 'PRIVACY_DATA.md';

    public function __construct(
        private string $phpstan_output_file,
        private string $components_base_dir,
        private bool $dry_run = false,
        private ?string $filter_component = null,
        private string $target_filename = self::DEFAULT_TARGET_FILENAME,
    ) {
    }

    /**
     * @return array<string, string> component => written file path
     */
    public function run(): array
    {
        $parser = new CollectorResultParser();
        $classifier = new PurposeClassifier();
        $renderer = new MarkdownRenderer();

        /** @var array<string, ComponentReport> $reports */
        $reports = [];
        foreach ($parser->parse($this->phpstan_output_file) as $raw) {
            $component = $raw['component'];
            if ($this->filter_component !== null && $component !== $this->filter_component) {
                continue;
            }
            $reports[$component] ??= new ComponentReport();
            $reports[$component]->add($classifier->classify($raw));
        }
        ksort($reports);

        $written = [];
        foreach ($reports as $component => $report) {
            $path = "{$this->components_base_dir}/{$component}/{$this->target_filename}";
            if (!$this->dry_run) {
                file_put_contents($path, $renderer->render($component, $report));
            }
            $written[$component] = $path;
        }
        return $written;
    }
}
