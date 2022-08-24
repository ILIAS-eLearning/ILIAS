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

/**
 * XML writer class
 * Class to simplify manual writing of xml documents.
 * It only supports writing xml sequentially, because the xml document
 * is saved in a string with no additional structure information.
 * The author is responsible for well-formedness and validity
 * of the xml document.
 * @author  Andreas Kordosz (akordosz@databay.de)
 * @ingroup ModulesFile
 */
class ilForumXMLWriter extends ilXmlWriter
{
    public ?int $forum_id = 0;
    private ?string $target_dir_relative;
    private ?string $target_dir_absolute;

    public function __construct()
    {
        parent::__construct();
    }

    public function setForumId(int $id): void
    {
        $this->forum_id = $id;
    }

    public function setFileTargetDirectories(string $a_rel, string $a_abs): void
    {
        $this->target_dir_relative = $a_rel;
        $this->target_dir_absolute = $a_abs;
    }

    public function start(): bool
    {
        global $DIC;
        $ilDB = $DIC->database();

        ilFileUtils::makeDir($this->target_dir_absolute . "/objects");

        $query_frm = '
            SELECT *
            FROM object_data od
            INNER JOIN frm_data
                ON top_frm_fk  = od.obj_id
            LEFT JOIN frm_settings fs 
                ON fs.obj_id = od.obj_id
            WHERE od.obj_id = ' . $ilDB->quote($this->forum_id, 'integer');

        $res = $ilDB->query($query_frm);
        $row = $ilDB->fetchObject($res);

        $this->xmlStartTag("Forum", null);

        $this->xmlElement("Id", null, (int) $row->top_pk);
        $this->xmlElement("ObjId", null, (int) $row->obj_id);
        $this->xmlElement("Title", null, $row->title);
        $this->xmlElement("Description", null, $row->description);
        $this->xmlElement("DefaultView", null, (int) $row->default_view);
        $this->xmlElement("Pseudonyms", null, (int) $row->anonymized);
        $this->xmlElement("Statistics", null, (int) $row->statistics_enabled);
        $this->xmlElement("ThreadRatings", null, (int) $row->thread_rating);
        $this->xmlElement("Sorting", null, (int) $row->thread_sorting);
        $this->xmlElement("MarkModeratorPosts", null, (int) $row->mark_mod_posts);
        $this->xmlElement("PostingActivation", null, (int) $row->post_activation);
        $this->xmlElement("PresetSubject", null, (int) $row->preset_subject);
        $this->xmlElement("PresetRe", null, (int) $row->add_re_subject);
        $this->xmlElement("NotificationType", null, $row->notification_type);
        $this->xmlElement("ForceNotification", null, (int) $row->admin_force_noti);
        $this->xmlElement("ToggleNotification", null, (int) $row->user_toggle_noti);
        $this->xmlElement("LastPost", null, $row->top_last_post);
        $this->xmlElement("Moderator", null, (int) $row->top_mods);
        $this->xmlElement("CreateDate", null, $row->top_date);
        $this->xmlElement("UpdateDate", null, $row->top_update);
        $this->xmlElement("FileUpload", null, (int) $row->file_upload_allowed);
        $this->xmlElement("UpdateUserId", null, $row->update_user);
        $this->xmlElement("UserId", null, (int) $row->top_usr_id);

        $query_thr = "SELECT frm_threads.* " .
            " FROM frm_threads " .
            " INNER JOIN frm_data ON top_pk = thr_top_fk " .
            'WHERE top_frm_fk = ' . $ilDB->quote($this->forum_id, 'integer');

        $res = $ilDB->query($query_thr);

        while ($row = $ilDB->fetchObject($res)) {
            $this->xmlStartTag("Thread");

            $this->xmlElement("Id", null, (int) $row->thr_pk);
            $this->xmlElement("Subject", null, $row->thr_subject);
            $this->xmlElement("UserId", null, (int) $row->thr_display_user_id);
            $this->xmlElement("AuthorId", null, (int) $row->thr_author_id);
            $this->xmlElement("Alias", null, $row->thr_usr_alias);
            $this->xmlElement("LastPost", null, $row->thr_last_post);
            $this->xmlElement("CreateDate", null, $row->thr_date);
            $this->xmlElement("UpdateDate", null, $row->thr_date);
            $this->xmlElement("ImportName", null, $row->import_name);
            $this->xmlElement("Sticky", null, (int) $row->is_sticky);
            $this->xmlElement("Closed", null, (int) $row->is_closed);

            $query = 'SELECT frm_posts.*, frm_posts_tree.*
						FROM frm_posts
							INNER JOIN frm_data
								ON top_pk = pos_top_fk
							INNER JOIN frm_posts_tree
								ON pos_fk = pos_pk
						WHERE pos_thr_fk = ' . $ilDB->quote($row->thr_pk, 'integer') . ' ';
            $query .= " ORDER BY frm_posts_tree.lft ASC";
            $resPosts = $ilDB->query($query);

            while ($rowPost = $ilDB->fetchObject($resPosts)) {
                $this->xmlStartTag("Post");
                $this->xmlElement("Id", null, (int) $rowPost->pos_pk);
                $this->xmlElement("UserId", null, (int) $rowPost->pos_display_user_id);
                $this->xmlElement("AuthorId", null, (int) $rowPost->pos_author_id);
                $this->xmlElement("Alias", null, $rowPost->pos_usr_alias);
                $this->xmlElement("Subject", null, $rowPost->pos_subject);
                $this->xmlElement("CreateDate", null, $rowPost->pos_date);
                $this->xmlElement("UpdateDate", null, $rowPost->pos_update);
                $this->xmlElement("UpdateUserId", null, (int) $rowPost->update_user);
                $this->xmlElement("Censorship", null, (int) $rowPost->pos_cens);
                $this->xmlElement("CensorshipMessage", null, $rowPost->pos_cens_com);
                $this->xmlElement("Notification", null, $rowPost->notify);
                $this->xmlElement("ImportName", null, $rowPost->import_name);
                $this->xmlElement("Status", null, (int) $rowPost->pos_status);
                $this->xmlElement("Message", null, ilRTE::_replaceMediaObjectImageSrc($rowPost->pos_message, 0));

                if ($rowPost->is_author_moderator === null) {
                    $is_moderator_string = 'NULL';
                } else {
                    $is_moderator_string = (string) $rowPost->is_author_moderator;
                }

                $this->xmlElement("isAuthorModerator", null, $is_moderator_string);

                $media_exists = false;
                $mobs = ilObjMediaObject::_getMobsOfObject('frm:html', (int) $rowPost->pos_pk);
                foreach ($mobs as $mob) {
                    $moblabel = "il_" . IL_INST_ID . "_mob_" . $mob;
                    if (ilObjMediaObject::_exists($mob)) {
                        if (!$media_exists) {
                            $this->xmlStartTag("MessageMediaObjects");
                            $media_exists = true;
                        }

                        $mob_obj = new ilObjMediaObject($mob);
                        $imgattrs = [
                            "label" => $moblabel,
                            "uri" => $this->target_dir_relative . "/objects/" . "il_" . IL_INST_ID . "_mob_" . $mob . "/" . $mob_obj->getTitle()
                        ];

                        $this->xmlElement("MediaObject", $imgattrs, null);
                        $mob_obj->exportFiles($this->target_dir_absolute);
                    }
                }
                if ($media_exists) {
                    $this->xmlEndTag("MessageMediaObjects");
                }

                $this->xmlElement("Lft", null, (int) $rowPost->lft);
                $this->xmlElement("Rgt", null, (int) $rowPost->rgt);
                $this->xmlElement("Depth", null, (int) $rowPost->depth);
                $this->xmlElement("ParentId", null, (int) $rowPost->parent_pos);

                $tmp_file_obj = new ilFileDataForum(
                    (int) $this->forum_id,
                    (int) $rowPost->pos_pk
                );

                if (count($tmp_file_obj->getFilesOfPost())) {
                    foreach ($tmp_file_obj->getFilesOfPost() as $file) {
                        $this->xmlStartTag("Attachment");

                        copy($file['path'], $this->target_dir_absolute . "/" . basename($file['path']));
                        $content = $this->target_dir_relative . "/" . basename($file['path']);
                        $this->xmlElement("Content", null, $content);

                        $this->xmlEndTag("Attachment");
                    }
                }

                $this->xmlEndTag("Post");
            }
            $this->xmlEndTag("Thread");
        }
        $this->xmlEndTag("Forum");

        return true;
    }

    public function getXML(): string
    {
        // Replace ascii code 11 characters because of problems with xml sax parser
        return str_replace('&#11;', '', $this->xmlDumpMem(false));
    }
}
