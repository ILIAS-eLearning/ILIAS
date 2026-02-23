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

use ILIAS\Language\Language;

class ilRTESettings
{
    private const ALL_AVAILABLE_TAGS = [
        'a',
        'blockquote',
        'br',
        'cite',
        'code',
        'dd',
        'div',
        'dl',
        'dt',
        'em',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'hr',
        'img',
        'li',
        'object',
        'ol',
        'p',
        'param',
        'pre',
        'span',
        'strike',
        'strong',
        'sub',
        'sup',
        'table',
        'td',
        'tr',
        'u',
        'ul',
        'ruby', // Ruby Annotation XHTML module
        'rbc',
        'rtc',
        'rb',
        'rt',
        'rp'
    ];

    private const DEFAULT_TAGS = [
        'a',
        'blockquote',
        'br',
        'cite',
        'code',
        'dd',
        'div',
        'dl',
        'dt',
        'em',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'hr',
        'img',
        'li',
        'ol',
        'p',
        'pre',
        'span',
        'strike',
        'strong',
        'sub',
        'sup',
        'u',
        'ul'
    ];

    private const DEFAULT_FORUM_AND_EXERCISE_TAGS = [
        'a',
        'blockquote',
        'br',
        'code',
        'div',
        'em',
        'img',
        'li',
        'ol',
        'p',
        'strong',
        'u',
        'ul',
        'span'
    ];

    private ilSetting $advanced_editing;

    public function __construct(
        private readonly Language $lng,
        private readonly ?ilObjUser $current_user = null
    ) {
        $this->advanced_editing = new ilSetting('advanced_editing');
    }

    public static function _getUsedHTMLTags(string $module = ''): array
    {
        $tags = (new ilSetting('advanced_editing'))->get('advanced_editing_used_html_tags_' . $module, '');

        if ($tags === '') {
            if ($module === 'frm_post' || $module === 'exc_ass') {
                return self::DEFAULT_FORUM_AND_EXERCISE_TAGS;
            }
            return self::DEFAULT_TAGS;
        }

        $usedtags = unserialize($tags, ['allowed_classes' => false]);
        if ($module !== 'frm_post') {
            return $usedtags;
        }

        if (!in_array('div', $usedtags, true)) {
            $usedtags[] = 'div';
        }

        if (!in_array('blockquote', $usedtags, true)) {
            $usedtags[] = 'blockquote';
        }

        return $usedtags;
    }

    public static function _getUsedHTMLTagsAsString(string $module = ''): string
    {
        return array_reduce(
            self::_getUsedHTMLTags(),
            static fn(string $c, string $v): string => $c . '<$v>',
            ''
        );
    }

    public function getRichTextEditor(): string
    {
        return $this->advanced_editing->get('advanced_editing_javascript_editor', '0');
    }

    public function setRichTextEditor(string $js_editor): void
    {
        $this->advanced_editing->set('advanced_editing_javascript_editor', $js_editor);
    }

    public function setUsedHTMLTags(
        array $html_tags,
        string $module
    ): void {
        if ($module === '') {
            return;
        }

        $auto_added_tags = [];
        if ($module === 'frm_post') {
            if (!in_array('div', $html_tags, true)) {
                $auto_added_tags[] = 'div';
            }

            if (!in_array('blockquote', $html_tags, true)) {
                $auto_added_tags[] = 'blockquote';
            }
        }

        $this->advanced_editing->set(
            'advanced_editing_used_html_tags_' . $module,
            serialize(array_merge($html_tags, $auto_added_tags))
        );

        if ($auto_added_tags !== []) {
            throw new ilAdvancedEditingRequiredTagsException(
                sprintf(
                    $this->lng->txt('advanced_editing_required_tags'),
                    implode(', ', $auto_added_tags)
                )
            );
        }
    }

    public function getAllAvailableHTMLTags(): array
    {
        return self::ALL_AVAILABLE_TAGS;
    }

    public function setRichTextEditorUserState(int $state): void
    {
        $this->current_user->writePref('show_rte', (string) $state);
    }

    public function getRichTextEditorUserState(): int
    {

        if ($this->current_user->getPref('show_rte') !== '') {
            return (int) $this->current_user->getPref('show_rte');
        }
        return 1;
    }
}
