<?php

declare(strict_types=1);

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

namespace ILIAS\News\Data;

use DateTimeImmutable;

/**
 * News Item DTO for transfer of news items
 */
class NewsItem
{
    public function __construct(
        protected int $id,
        protected string $title,
        protected string $content,
        protected int $context_obj_id,
        protected string $context_obj_type,
        protected int $context_sub_obj_id,
        protected ?string $context_sub_obj_type,
        protected string $content_type,
        protected \DateTimeImmutable $creation_date,
        protected \DateTimeImmutable $update_date,
        protected int $user_id,
        protected int $update_user_id,
        protected string $visibility,
        protected string $content_long,
        protected int $priority,
        protected bool $content_is_lang_var,
        protected bool $content_text_is_lang_var,
        protected int $mob_id,
        protected string $playtime,
        protected int $mob_cnt_play,
        protected int $mob_cnt_download,
        protected bool $content_html
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): NewsItem
    {
        $this->id = $id;
        return $this;
    }

    public function isContentTextIsLangVar(): bool
    {
        return $this->content_text_is_lang_var;
    }

    public function setContentTextIsLangVar(bool $content_text_is_lang_var): NewsItem
    {
        $this->content_text_is_lang_var = $content_text_is_lang_var;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): NewsItem
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): NewsItem
    {
        $this->content = $content;
        return $this;
    }

    public function getContextObjId(): int
    {
        return $this->context_obj_id;
    }

    public function setContextObjId(int $context_obj_id): NewsItem
    {
        $this->context_obj_id = $context_obj_id;
        return $this;
    }

    public function getContextObjType(): string
    {
        return $this->context_obj_type;
    }

    public function setContextObjType(string $context_obj_type): NewsItem
    {
        $this->context_obj_type = $context_obj_type;
        return $this;
    }

    public function getContextSubObjId(): int
    {
        return $this->context_sub_obj_id;
    }

    public function setContextSubObjId(int $context_sub_obj_id): NewsItem
    {
        $this->context_sub_obj_id = $context_sub_obj_id;
        return $this;
    }

    public function getContextSubObjType(): ?string
    {
        return $this->context_sub_obj_type;
    }

    public function setContextSubObjType(?string $context_sub_obj_type): NewsItem
    {
        $this->context_sub_obj_type = $context_sub_obj_type;
        return $this;
    }

    public function getContentType(): string
    {
        return $this->content_type;
    }

    public function setContentType(string $content_type): NewsItem
    {
        $this->content_type = $content_type;
        return $this;
    }

    public function getCreationDate(): DateTimeImmutable
    {
        return $this->creation_date;
    }

    public function setCreationDate(DateTimeImmutable $creation_date): NewsItem
    {
        $this->creation_date = $creation_date;
        return $this;
    }

    public function getUpdateDate(): DateTimeImmutable
    {
        return $this->update_date;
    }

    public function setUpdateDate(DateTimeImmutable $update_date): NewsItem
    {
        $this->update_date = $update_date;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): NewsItem
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getUpdateUserId(): int
    {
        return $this->update_user_id;
    }

    public function setUpdateUserId(int $update_user_id): NewsItem
    {
        $this->update_user_id = $update_user_id;
        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): NewsItem
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function getContentLong(): string
    {
        return $this->content_long;
    }

    public function setContentLong(string $content_long): NewsItem
    {
        $this->content_long = $content_long;
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): NewsItem
    {
        $this->priority = $priority;
        return $this;
    }

    public function isContentIsLangVar(): bool
    {
        return $this->content_is_lang_var;
    }

    public function setContentIsLangVar(bool $content_is_lang_var): NewsItem
    {
        $this->content_is_lang_var = $content_is_lang_var;
        return $this;
    }

    public function getMobId(): int
    {
        return $this->mob_id;
    }

    public function setMobId(int $mob_id): NewsItem
    {
        $this->mob_id = $mob_id;
        return $this;
    }

    public function getPlaytime(): string
    {
        return $this->playtime;
    }

    public function setPlaytime(string $playtime): NewsItem
    {
        $this->playtime = $playtime;
        return $this;
    }

    public function getMobCntPlay(): int
    {
        return $this->mob_cnt_play;
    }

    public function setMobCntPlay(int $mob_cnt_play): NewsItem
    {
        $this->mob_cnt_play = $mob_cnt_play;
        return $this;
    }

    public function getMobCntDownload(): int
    {
        return $this->mob_cnt_download;
    }

    public function setMobCntDownload(int $mob_cnt_download): NewsItem
    {
        $this->mob_cnt_download = $mob_cnt_download;
        return $this;
    }

    public function isContentHtml(): bool
    {
        return $this->content_html;
    }

    public function setContentHtml(bool $content_html): NewsItem
    {
        $this->content_html = $content_html;
        return $this;
    }
}
