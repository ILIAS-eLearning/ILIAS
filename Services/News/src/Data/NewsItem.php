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
        protected bool $content_html,
        protected int $context_ref_id = 0
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isContentTextIsLangVar(): bool
    {
        return $this->content_text_is_lang_var;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getContextObjId(): int
    {
        return $this->context_obj_id;
    }

    public function getContextRefId(): int
    {
        return $this->context_ref_id;
    }

    public function getContextObjType(): string
    {
        return $this->context_obj_type;
    }

    public function getContextSubObjId(): int
    {
        return $this->context_sub_obj_id;
    }

    public function getContextSubObjType(): ?string
    {
        return $this->context_sub_obj_type;
    }

    public function getContentType(): string
    {
        return $this->content_type;
    }

    public function getCreationDate(): DateTimeImmutable
    {
        return $this->creation_date;
    }

    public function getUpdateDate(): DateTimeImmutable
    {
        return $this->update_date;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUpdateUserId(): int
    {
        return $this->update_user_id;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getContentLong(): string
    {
        return $this->content_long;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function isContentIsLangVar(): bool
    {
        return $this->content_is_lang_var;
    }

    public function getMobId(): int
    {
        return $this->mob_id;
    }

    public function getPlaytime(): string
    {
        return $this->playtime;
    }

    public function getMobCntPlay(): int
    {
        return $this->mob_cnt_play;
    }

    public function getMobCntDownload(): int
    {
        return $this->mob_cnt_download;
    }

    public function isContentHtml(): bool
    {
        return $this->content_html;
    }


    public function withContextRefId(int $context_ref_id): NewsItem
    {
        $clone = clone $this;
        $clone->context_ref_id = $context_ref_id;
        return $clone;
    }

    public function withContent(string $content): NewsItem
    {
        $clone = clone $this;
        $clone->content = $content;
        return $clone;
    }

    public function withContentLong(string $content_long): NewsItem
    {
        $clone = clone $this;
        $clone->content_long = $content_long;
        return $clone;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'context_obj_id' => $this->context_obj_id,
            'context_obj_type' => $this->context_obj_type,
            'context_sub_obj_id' => $this->context_sub_obj_id,
            'context_sub_obj_type' => $this->context_sub_obj_type,
            'content_type' => $this->content_type,
            'creation_date' => $this->creation_date->format('Y-m-d H:i:s'),
            'update_date' => $this->update_date->format('Y-m-d H:i:s'),
            'user_id' => $this->user_id,
            'update_user_id' => $this->update_user_id,
            'visibility' => $this->visibility,
            'content_long' => $this->content_long,
            'priority' => $this->priority,
            'content_is_lang_var' => $this->content_is_lang_var,
            'content_text_is_lang_var' => $this->content_text_is_lang_var,
            'mob_id' => $this->mob_id,
            'playtime' => $this->playtime,
            'mob_cnt_play' => $this->mob_cnt_play,
            'mob_cnt_download' => $this->mob_cnt_download,
            'content_html' => $this->content_html
        ];
    }

    public function toLegacy(): \ilNewsItem
    {
        $item = new \ilNewsItem(0); //prevent database loading
        $item->setId($this->id);
        $item->setTitle($this->title);
        $item->setContent($this->content);
        $item->setContextObjId($this->context_obj_id);
        $item->setContextObjType($this->context_obj_type);
        $item->setContextSubObjId($this->context_sub_obj_id);
        $item->setContextSubObjType($this->context_sub_obj_type);
        $item->setContentType($this->content_type);
        $item->setCreationDate($this->creation_date->format('Y-m-d H:i:s'));
        $item->setUpdateDate($this->update_date->format('Y-m-d H:i:s'));
        $item->setUserId($this->user_id);
        $item->setUpdateUserId($this->update_user_id);
        $item->setVisibility($this->visibility);
        $item->setContentLong($this->content_long);
        $item->setPriority($this->priority);
        $item->setContentIsLangVar($this->content_is_lang_var);
        $item->setContentTextIsLangVar($this->content_text_is_lang_var);
        $item->setMobId($this->mob_id);
        $item->setPlaytime($this->playtime);
        $item->setMobPlayCounter($this->mob_cnt_play);
        $item->setMobDownloadCounter($this->mob_cnt_download);
        $item->setContentHtml($this->content_html);
        return $item;
    }
}
