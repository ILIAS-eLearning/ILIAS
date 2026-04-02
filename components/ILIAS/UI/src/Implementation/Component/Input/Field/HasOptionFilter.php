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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use Exception;

/**
 * @see HasOptionFilterInternal for interface definition
 */
trait HasOptionFilter
{
    protected bool $has_option_filter = false;
    protected string $options_data_source = '';
    protected string $options_data_source_token = 'term';
    protected string $options_data_source_display_value_token = 'display_values';
    protected int $options_data_source_suggestion_start = self::OPTIONS_DATA_SOURCE_SUGGESTION_START;

    /** @var array<string, string> (value => label) */
    protected array $options = [];

    /**
     * @throws Exception
     */
    public function withHasOptionFilter(
        bool $has_option_filter = true,
        string $options_data_source = '',
        string $options_data_source_token = 'term',
        string $options_data_source_display_value_token = 'display_values',
        int $options_data_source_suggestion_start = self::OPTIONS_DATA_SOURCE_SUGGESTION_START
    ): static {
        if ($this->getOptions() !== [] && $options_data_source) {
            throw new Exception('Input should not have data source when using options');
        }
        $clone = clone $this;
        $clone->has_option_filter = $has_option_filter;
        $clone->options_data_source = $options_data_source;
        $clone->options_data_source_token = $options_data_source_token;
        $clone->options_data_source_display_value_token = $options_data_source_display_value_token;
        $clone->options_data_source_suggestion_start = $options_data_source_suggestion_start;
        return $clone;
    }

    public function hasOptionFilter(): bool
    {
        return $this->has_option_filter;
    }

    public function getOptionsDataSource(): string
    {
        return $this->options_data_source;
    }

    public function getOptionsDataSourceToken(): string
    {
        return $this->options_data_source_token;
    }

    public function getOptionsDataSourceDisplayValueToken(): string
    {
        return $this->options_data_source_display_value_token;
    }

    public function getOptionsDataSourceSuggestionStart(): int
    {
        return $this->options_data_source_suggestion_start;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
