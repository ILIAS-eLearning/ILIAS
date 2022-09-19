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
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ModulesForum
 */
class ilForumTopic
{
    private int $id;
    private int $forum_id = 0;
    private int $frm_obj_id = 0;
    private int $display_user_id = 0;
    private ?string $user_alias = null;
    private string $subject = '';
    private ?string $createdate = null;
    private ?string $changedate = null;
    private int $num_posts = 0;
    private ?string $last_post_string = null;
    private int $visits = 0;
    private ?string $import_name = null;
    private bool $is_sticky = false;
    private bool $is_closed = false;
    private string $orderField = '';
    private ?ilForumPost $last_post = null;
    private ilDBInterface $db;
    private bool $is_moderator;
    private int $thr_author_id = 0;
    private float $average_rating = 0.0;
    private string $orderDirection = 'DESC';
    protected static array $possibleOrderDirections = ['ASC', 'DESC'];
    private ilObjUser $user;
    private int $num_new_posts = 0;
    private int $num_unread_posts = 0;
    private bool $user_notification_enabled = false;

    /**
     * Returns an object of a forum topic. The constructor calls the private method read()
     * to load the topic data from database into the object.
     * @param int $a_id primary key of a forum topic (optional)
     * @param bool $a_is_moderator moderator-status of the current user (optional)
     * @param bool $preventImplicitRead Prevents the implicit database query if an id was passed
     */
    public function __construct(int $a_id = 0, bool $a_is_moderator = false, bool $preventImplicitRead = false)
    {
        global $DIC;

        $this->is_moderator = $a_is_moderator;
        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->id = $a_id;

        if (!$preventImplicitRead) {
            $this->read();
        }
    }

    public function assignData(array $data): void
    {
        $this->setId((int) $data['thr_pk']);
        $this->setForumId((int) $data['thr_top_fk']);
        $this->setSubject($data['thr_subject']);
        $this->setDisplayUserId((int) $data['thr_display_user_id']);
        $this->setUserAlias($data['thr_usr_alias']);
        $this->setLastPostString($data['thr_last_post']);
        $this->setCreateDate($data['thr_date']);
        $this->setChangeDate($data['thr_update']);
        $this->setVisits((int) $data['visits']);
        $this->setImportName($data['import_name']);
        $this->setSticky((bool) $data['is_sticky']);
        $this->setClosed((bool) $data['is_closed']);
        $this->setAverageRating(isset($data['avg_rating']) ? (float) $data['avg_rating'] : 0);
        $this->setThrAuthorId((int) $data['thr_author_id']);

        // Aggregated values
        if (isset($data['num_posts'])) {
            $this->setNumPosts((int) $data['num_posts']);
        }
        if (isset($data['num_unread_posts'])) {
            $this->setNumUnreadPosts((int) $data['num_unread_posts']);
        }
        if (isset($data['num_new_posts'])) {
            $this->setNumNewPosts((int) $data['num_new_posts']);
        }
        if (isset($data['usr_notification_is_enabled'])) {
            $this->setUserNotificationEnabled((bool) $data['usr_notification_is_enabled']);
        }
    }

    public function insert(): bool
    {
        if ($this->forum_id) {
            $nextId = $this->db->nextId('frm_threads');

            $this->db->insert(
                'frm_threads',
                [
                    'thr_pk' => ['integer', $nextId],
                    'thr_top_fk' => ['integer', $this->forum_id],
                    'thr_subject' => ['text', $this->subject],
                    'thr_display_user_id' => ['integer', $this->display_user_id],
                    'thr_usr_alias' => ['text', $this->user_alias],
                    'thr_num_posts' => ['integer', $this->num_posts],
                    'thr_last_post' => ['text', $this->last_post_string],
                    'thr_date' => ['timestamp', $this->createdate],
                    'thr_update' => ['timestamp', null],
                    'import_name' => ['text', $this->import_name],
                    'is_sticky' => ['integer', (int) $this->is_sticky],
                    'is_closed' => ['integer', (int) $this->is_closed],
                    'avg_rating' => ['text', (string) $this->average_rating],
                    'thr_author_id' => ['integer', $this->thr_author_id]
                ]
            );

            $this->id = $nextId;

            return true;
        }

        return false;
    }

    public function update(): bool
    {
        if ($this->id) {
            $this->db->manipulateF(
                '
				UPDATE frm_threads
				SET thr_top_fk = %s,
					thr_subject = %s,
					thr_update = %s,
					thr_num_posts = %s,
					thr_last_post = %s,
					avg_rating = %s
				WHERE thr_pk = %s',
                ['integer', 'text', 'timestamp', 'integer', 'text', 'text', 'integer'],
                [
                    $this->forum_id,
                    $this->subject,
                    date('Y-m-d H:i:s'),
                    $this->num_posts,
                    $this->last_post_string,
                    (string) $this->average_rating,
                    $this->id
                ]
            );

            return true;
        }

        return false;
    }

    private function read(): bool
    {
        if ($this->id) {
            $res = $this->db->queryF(
                '
				SELECT frm_threads.*, top_frm_fk frm_obj_id
				FROM frm_threads
				INNER JOIN frm_data ON top_pk = thr_top_fk
				WHERE thr_pk = %s',
                ['integer'],
                [$this->id]
            );

            $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

            if (is_object($row)) {
                $this->forum_id = (int) $row->thr_top_fk;
                $this->display_user_id = (int) $row->thr_display_user_id;
                $this->user_alias = $row->thr_usr_alias;
                $this->subject = html_entity_decode((string) $row->thr_subject);
                $this->createdate = $row->thr_date;
                $this->changedate = $row->thr_update;
                $this->import_name = $row->import_name;
                $this->num_posts = (int) $row->thr_num_posts;
                $this->last_post_string = $row->thr_last_post;
                $this->visits = (int) $row->visits;
                $this->is_sticky = (bool) $row->is_sticky;
                $this->is_closed = (bool) $row->is_closed;
                $this->frm_obj_id = (int) $row->frm_obj_id;
                $this->average_rating = (float) $row->avg_rating;
                $this->thr_author_id = (int) $row->thr_author_id;

                return true;
            }
            $this->id = 0;
            return false;
        }

        return false;
    }

    public function reload(): bool
    {
        return $this->read();
    }

    public function getPostRootId(): int
    {
        $this->db->setLimit(1);
        $res = $this->db->queryF(
            'SELECT pos_fk FROM frm_posts_tree WHERE thr_fk = %s AND parent_pos = %s AND depth = %s ORDER BY rgt DESC',
            ['integer', 'integer', 'integer'],
            [$this->id, 0, 1]
        );

        if ($row = $this->db->fetchObject($res)) {
            return (int) $row->pos_fk ?: 0;
        }
        return 0;
    }

    public function getFirstVisiblePostId(): int
    {
        $this->db->setLimit(1);
        $res = $this->db->queryF(
            'SELECT pos_fk FROM frm_posts_tree WHERE thr_fk = %s AND parent_pos != %s AND depth = %s ORDER BY rgt DESC',
            ['integer', 'integer', 'integer'],
            [$this->id, 0, 2]
        );

        if ($row = $this->db->fetchObject($res)) {
            return (int) $row->pos_fk ?: 0;
        }
        return 0;
    }

    public function updateVisits(): void
    {
        $checkTime = time() - (60 * 60);

        if (ilSession::get('frm_visit_frm_threads_' . $this->id) < $checkTime) {
            ilSession::set('frm_visit_frm_threads_' . $this->id, time());

            $this->db->manipulateF(
                'UPDATE frm_threads SET visits = visits + 1 WHERE thr_pk = %s',
                ['integer'],
                [$this->id]
            );
        }
    }

    public function countPosts(bool $ignoreRoot = false): int
    {
        $res = $this->db->queryF(
            '
			SELECT COUNT(*) cnt
			FROM frm_posts
			INNER JOIN frm_posts_tree ON frm_posts_tree.pos_fk = pos_pk
			WHERE pos_thr_fk = %s' . ($ignoreRoot ? ' AND parent_pos != 0 ' : ''),
            ['integer'],
            [$this->id]
        );

        $row = $this->db->fetchAssoc($res);
        if (is_array($row)) {
            return (int) $row['cnt'];
        }

        return 0;
    }

    public function countActivePosts(bool $ignoreRoot = false): int
    {
        $res = $this->db->queryF(
            '
			SELECT COUNT(*) cnt
			FROM frm_posts
			INNER JOIN frm_posts_tree ON frm_posts_tree.pos_fk = pos_pk
			WHERE (pos_status = %s
				 OR (pos_status = %s AND pos_display_user_id = %s))
			AND pos_thr_fk = %s' . ($ignoreRoot ? ' AND parent_pos != 0 ' : ''),
            ['integer', 'integer', 'integer', 'integer'],
            ['1', '0', $this->user->getId(), $this->id]
        );

        $row = $this->db->fetchAssoc($res);
        if (is_array($row)) {
            return (int) $row['cnt'];
        }

        return 0;
    }

    public function getPostRootNode(bool $isModerator = false, bool $preventImplicitRead = false): ilForumPost
    {
        $this->db->setLimit(1);
        $res = $this->db->queryF(
            '
			SELECT *
			FROM frm_posts 
			INNER JOIN frm_posts_tree ON pos_fk = pos_pk
			WHERE parent_pos = %s
			AND thr_fk = %s
			ORDER BY rgt DESC',
            ['integer', 'integer'],
            [0, $this->id]
        );

        if ($row = $this->db->fetchAssoc($res)) {
            $post = new ilForumPost((int) $row['pos_pk'], $isModerator, $preventImplicitRead);
            $post->assignData($row);
            return $post;
        }

        throw new OutOfBoundsException(sprintf('Could not find first posting by id: %s', $this->id));
    }

    public function getFirstVisiblePostNode(bool $isModerator = false, bool $preventImplicitRead = false): ilForumPost
    {
        $this->db->setLimit(1);
        $res = $this->db->queryF(
            '
			SELECT *
			FROM frm_posts 
			INNER JOIN frm_posts_tree ON pos_fk = pos_pk
			WHERE parent_pos != %s
			AND thr_fk = %s
			AND depth = %s
			ORDER BY rgt DESC',
            ['integer', 'integer', 'integer'],
            [0, $this->id, 2]
        );

        if ($row = $this->db->fetchAssoc($res)) {
            $post = new ilForumPost((int) $row['pos_pk'], $isModerator, $preventImplicitRead);
            $post->assignData($row);
            return $post;
        }

        throw new OutOfBoundsException(sprintf('Could not find first posting by id: %s', $this->id));
    }

    public function getLastPost(): ilForumPost
    {
        if ($this->id) {
            $this->db->setLimit(1);
            $res = $this->db->queryF(
                'SELECT pos_pk FROM frm_posts WHERE pos_thr_fk = %s ORDER BY pos_date DESC',
                ['integer'],
                [$this->id]
            );

            if ($row = $this->db->fetchObject($res)) {
                return new ilForumPost((int) $row->pos_pk);
            }
        }

        throw new OutOfBoundsException(sprintf('Could not find last posting by id: %s', $this->id));
    }

    public function getLastActivePost(): ilForumPost
    {
        if ($this->id) {
            $this->db->setLimit(1);
            $res = $this->db->queryF(
                '
				SELECT pos_pk
				FROM frm_posts 
				WHERE pos_thr_fk = %s
				AND (pos_status = %s OR (pos_status = %s AND pos_display_user_id = %s))
				ORDER BY pos_date DESC',
                ['integer', 'integer', 'integer', 'integer'],
                [$this->id, '1', '0', $this->user->getId()]
            );

            if ($row = $this->db->fetchObject($res)) {
                return new ilForumPost((int) $row->pos_pk);
            }
        }

        throw new OutOfBoundsException(sprintf('Could not find last active posting by id: %s', $this->id));
    }

    /**
     * @return array<int, int>
     */
    public function getAllPostIds(): array
    {
        $posts = [];

        if ($this->id) {
            $res = $this->db->queryF('SELECT pos_pk FROM frm_posts WHERE pos_thr_fk = %s', ['integer'], [$this->id]);

            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $posts[(int) $row->pos_pk] = (int) $row->pos_pk;
            }
        }

        return $posts;
    }

    /**
     * Fetches and returns an array of posts from the post tree, starting with the node object passed by
     * the first paramter.
     * @param ilForumPost $a_post_node node-object of a post
     * @return ilForumPost[] Array of post objects
     */
    public function getPostTree(ilForumPost $a_post_node): array
    {
        $posts = [];
        $data = [];
        $data_types = [];

        if ($a_post_node->getLft() > 1) {
            $dummy_root_condition = 'lft >= %s AND lft < %s';
        } else {
            $dummy_root_condition = 'lft > %s AND lft < %s';
        }

        $query = '
			SELECT 			is_author_moderator, pos_author_id, pos_pk, fpt_date, rgt, pos_top_fk, pos_thr_fk, 
							pos_display_user_id, pos_usr_alias, pos_subject,
							pos_status, pos_message, pos_date, pos_update,
							update_user, pos_cens, pos_cens_com, notify,
							import_name, fpt_pk, parent_pos, lft, depth,
							(CASE
							WHEN fur.post_id IS NULL ' .
            ($this->user->getId() === ANONYMOUS_USER_ID ? ' AND 1 = 2 ' : '') . '
							THEN 0
							ELSE 1
							END) post_read,
							firstname, lastname, title, login
							 
			FROM 			frm_posts_tree
			 
			INNER JOIN 		frm_posts 
				ON 			pos_fk = pos_pk
				
			LEFT JOIN		usr_data
				ON			pos_display_user_id  = usr_id
				
			LEFT JOIN		frm_user_read fur
				ON			fur.thread_id = pos_thr_fk
				AND			fur.post_id = pos_pk
				AND			fur.usr_id = %s
				 
			WHERE 			' . $dummy_root_condition . '
				AND 		thr_fk = %s';

        array_push($data_types, 'integer', 'integer', 'integer', 'integer');
        array_push(
            $data,
            $this->user->getId(),
            $a_post_node->getLft(),
            $a_post_node->getRgt(),
            $a_post_node->getThreadId()
        );

        if ($this->orderField !== '') {
            $query .= " ORDER BY " . $this->orderField . " " . $this->getOrderDirection();
        }

        $res = $this->db->queryF($query, $data_types, $data);

        $usr_ids = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $post = new ilForumPost((int) $row['pos_pk'], false, true);
            $post->assignData($row);

            if (!$this->is_moderator && !$post->isActivated() && $post->getPosAuthorId() !== $this->user->getId()) {
                continue;
            }

            if ((int) $row['pos_display_user_id']) {
                $usr_ids[(int) $row['pos_display_user_id']] = (int) $row['pos_display_user_id'];
            }
            if ((int) $row['update_user']) {
                $usr_ids[(int) $row['update_user']] = (int) $row['update_user'];
            }

            $posts[] = $post;
        }

        ilForumAuthorInformationCache::preloadUserObjects(array_values($usr_ids));

        return $posts;
    }

    /**
     * Moves all posts within the current thread to a new forum
     * @param int $old_obj_id object id of the current forum
     * @param int $old_pk primary key of old forum
     * @param int $new_obj_id object id of the new forum
     * @param int $new_pk primary key of new forum
     * @return int Number of afffected rows by updating posts
     * @throws ilFileUtilsException
     */
    public function movePosts(int $old_obj_id, int $old_pk, int $new_obj_id, int $new_pk): int
    {
        if (!$this->id) {
            return 0;
        }

        $post_ids = $this->getAllPostIds();
        $postsMoved = [];
        try {
            foreach ($post_ids as $post_id) {
                $file_obj = new ilFileDataForum($old_obj_id, $post_id);
                $moved = $file_obj->moveFilesOfPost($new_obj_id);

                if (true === $moved) {
                    $postsMoved[] = [
                        'from' => $old_obj_id,
                        'to' => $new_obj_id,
                        'position_id' => $post_id
                    ];
                }

                unset($file_obj);
            }
        } catch (ilFileUtilsException $exception) {
            foreach ($postsMoved as $postedInformation) {
                $file_obj = new ilFileDataForum($postedInformation['to'], $postedInformation['position_id']);
                $file_obj->moveFilesOfPost($postedInformation['from']);
            }

            throw $exception;
        }

        $current_id = $this->id;

        $ilAtomQuery = $this->db->buildAtomQuery();
        $ilAtomQuery->addTableLock('frm_user_read');
        $ilAtomQuery->addTableLock('frm_thread_access');

        $ilAtomQuery->addQueryCallable(static function (ilDBInterface $ilDB) use ($new_obj_id, $current_id): void {
            $ilDB->manipulateF(
                'DELETE FROM frm_user_read WHERE obj_id = %s AND thread_id =%s',
                ['integer', 'integer'],
                [$new_obj_id, $current_id]
            );

            $ilDB->manipulateF(
                'UPDATE frm_user_read SET obj_id = %s WHERE thread_id = %s',
                ['integer', 'integer'],
                [$new_obj_id, $current_id]
            );

            $ilDB->manipulateF(
                'DELETE FROM frm_thread_access WHERE obj_id = %s AND thread_id = %s',
                ['integer', 'integer'],
                [$new_obj_id, $current_id]
            );

            $ilDB->manipulateF(
                'UPDATE frm_thread_access SET obj_id = %s WHERE thread_id =%s',
                ['integer', 'integer'],
                [$new_obj_id, $current_id]
            );
        });

        $ilAtomQuery->run();

        $this->db->manipulateF(
            'UPDATE frm_posts SET pos_top_fk = %s WHERE pos_thr_fk = %s',
            ['integer', 'integer'],
            [$new_pk, $this->id]
        );

        $res = $this->db->queryF(
            'SELECT * FROM frm_posts WHERE pos_thr_fk = %s',
            ['integer'],
            [$this->id]
        );

        $old_obj_id = ilForum::_lookupObjIdForForumId($old_pk);
        $new_obj_id = ilForum::_lookupObjIdForForumId($new_pk);

        while ($post = $this->db->fetchAssoc($res)) {
            $news_id = ilNewsItem::getFirstNewsIdForContext(
                $old_obj_id,
                'frm',
                (int) $post['pos_pk'],
                'pos'
            );
            $news_item = new ilNewsItem($news_id);
            $news_item->setContextObjId($new_obj_id);
            $news_item->update();
        }

        return count($post_ids);
    }

    public function getNestedSetPostChildren(?int $pos_id = null, ?int $num_levels = null): array
    {
        $data = null;
        $objProperties = ilForumProperties::getInstance($this->getFrmObjId());
        $is_post_activation_enabled = $objProperties->isPostActivationEnabled();

        if ($pos_id !== null) {
            $res = $this->db->queryF(
                "
				SELECT		lft, rgt, depth
				FROM		frm_posts_tree
				WHERE		pos_fk = %s
				AND			thr_fk = %s",
                ['integer', 'integer'],
                [$pos_id, $this->id]
            );

            $data = $this->db->fetchAssoc($res);
        }

        $query = '
			SELECT			fpt.depth,
							fpt.rgt,
							fpt.parent_pos,
							fp.pos_pk,
							fp.pos_subject,
							fp.pos_usr_alias,
							fp.pos_date,
							fp.pos_update,
							fp.pos_status,
							fp.pos_display_user_id,
							fp.pos_usr_alias,
							fp.import_name,
							fp.pos_author_id,
							fp.is_author_moderator,
							fur.post_id,
							(CASE
							WHEN fur.post_id IS NULL ' .
            ($this->user->getId() === ANONYMOUS_USER_ID ? ' AND 1 = 2 ' : '') . '
							THEN 0
							ELSE 1
							END) post_read,
							COUNT(fpt2.pos_fk) children

			FROM			frm_posts_tree fpt

			INNER JOIN		frm_posts fp
				ON			fp.pos_pk = fpt.pos_fk
				
			LEFT JOIN		frm_posts_tree fpt2
				 ON         fpt2.lft BETWEEN fpt.lft AND fpt.rgt
				 AND		fpt.thr_fk = fpt2.thr_fk
				 AND		fpt.pos_fk != fpt2.pos_fk ';

        $query .= '
			LEFT JOIN		frm_user_read fur
				ON			fur.thread_id = fp.pos_thr_fk
				AND			fur.post_id = fp.pos_pk
				AND			fur.usr_id = ' . $this->db->quote($this->user->getId(), 'integer') . '

			LEFT JOIN		usr_data ud
				ON			ud.usr_id = fp.pos_display_user_id
		
			WHERE			fpt.thr_fk = ' . $this->db->quote($this->id, 'integer');

        if ($data) {
            $query .= '		AND fpt.lft > ' . $this->db->quote($data['lft'], 'integer') .
                '		AND fpt.lft < ' . $this->db->quote($data['rgt'], 'integer') . ' ';
        }
        if ($is_post_activation_enabled && !$this->is_moderator) {
            $query .= ' AND (fp.pos_status = 1 OR fp.pos_status = 0 AND fp.pos_display_user_id = ' . $this->db->quote(
                $this->user->getId(),
                'integer'
            ) . ') ';
        }

        if ($data && is_numeric($num_levels)) {
            $query .= ' AND fpt.depth <= ' . $this->db->quote((int) $data['depth'] + $num_levels, 'integer') . ' ';
        }

        $query .= ' GROUP BY fpt.depth,
							fpt.rgt,
							fpt.parent_pos,
							fp.pos_pk,
							fp.pos_subject,
							fp.pos_usr_alias,
							fp.pos_date,
							fp.pos_update,
							fp.pos_status,
							fp.pos_display_user_id,
							fp.pos_usr_alias,
							fp.import_name,
							fp.pos_author_id,
							fp.is_author_moderator,
							fur.post_id
					ORDER BY fpt.rgt DESC
		';

        $queryCounter = '
			SELECT			pos_fk
			FROM			frm_posts_tree fpt
			INNER JOIN		frm_posts fp
				ON			fp.pos_pk = fpt.pos_fk
			WHERE			fpt.thr_fk = ' . $this->db->quote($this->id, 'integer');

        if ($is_post_activation_enabled && !$this->is_moderator) {
            $queryCounter .= ' AND (fp.pos_status = 1 OR fp.pos_status = 0 AND fp.pos_display_user_id = ' . $this->db->quote(
                $this->user->getId(),
                'integer'
            ) . ') ';
        }
        $queryCounter .= ' ORDER BY fpt.rgt DESC';

        $resCounter = $this->db->query($queryCounter);
        $counter = [];
        $i = 0;
        while ($row = $this->db->fetchAssoc($resCounter)) {
            $counter[(int) $row['pos_fk']] = $i++;
        }

        $res = $this->db->query($query);
        $children = [];
        $usr_ids = [];
        while ($row = $this->db->fetchAssoc($res)) {
            if ((int) $row['pos_display_user_id']) {
                $usr_ids[] = (int) $row['pos_display_user_id'];
            }

            $row['counter'] = $counter[$row['pos_pk']];
            $casted_row = [];
            $casted_row['depth'] = (int) $row['depth'];
            $casted_row['rgt'] = (int) $row['rgt'];
            $casted_row['parent_pos'] = (int) $row['parent_pos'];
            $casted_row['pos_pk'] = (int) $row['pos_pk'];
            $casted_row['pos_subject'] = (string) $row['pos_subject'];
            $casted_row['pos_usr_alias'] = (string) $row['pos_usr_alias'];
            $casted_row['pos_date'] = (string) $row['pos_date'];
            $casted_row['pos_update'] = (string) $row['pos_update'];
            $casted_row['pos_status'] = (int) $row['pos_status'];
            $casted_row['pos_display_user_id'] = (int) $row['pos_display_user_id'];
            $casted_row['import_name'] = (string) $row['import_name'];
            $casted_row['pos_author_id'] = (int) $row['pos_author_id'];
            $casted_row['is_author_moderator'] = (int) $row['is_author_moderator'];
            $casted_row['post_id'] = (int) $row['post_id'];
            $casted_row['post_read'] = (int) $row['post_read'];
            $casted_row['children'] = (int) $row['children'];

            $children[] = $casted_row;
        }

        ilForumAuthorInformationCache::preloadUserObjects(array_unique($usr_ids));

        return $children;
    }

    public function isNotificationEnabled(int $a_user_id): bool
    {
        if ($this->id && $a_user_id) {
            $result = $this->db->queryF(
                'SELECT COUNT(notification_id) cnt FROM frm_notification WHERE user_id = %s AND thread_id = %s',
                ['integer', 'integer'],
                [$a_user_id, $this->id]
            );

            if ($row = $this->db->fetchAssoc($result)) {
                return (int) $row['cnt'] > 0;
            }

            return false;
        }

        return false;
    }

    public function enableNotification(int $a_user_id): void
    {
        if ($this->id && $a_user_id && !$this->isNotificationEnabled($a_user_id)) {
            $nextId = $this->db->nextId('frm_notification');
            $this->db->manipulateF(
                '
                INSERT INTO frm_notification
                (	notification_id,
                    user_id,
                    thread_id
                )
                VALUES(%s, %s, %s)',
                ['integer', 'integer', 'integer'],
                [$nextId, $a_user_id, $this->id]
            );
        }
    }

    public function disableNotification(int $a_user_id): void
    {
        if ($this->id && $a_user_id) {
            $this->db->manipulateF(
                'DELETE FROM frm_notification WHERE user_id = %s AND thread_id = %s',
                ['integer', 'integer'],
                [$a_user_id, $this->id]
            );
        }
    }

    public function makeSticky(): bool
    {
        if ($this->id && !$this->is_sticky) {
            $this->db->manipulateF(
                'UPDATE frm_threads SET is_sticky = %s WHERE thr_pk = %s',
                ['integer', 'integer'],
                [1, $this->id]
            );

            $this->is_sticky = true;
            return true;
        }

        return false;
    }

    public function unmakeSticky(): bool
    {
        if ($this->id && $this->is_sticky) {
            $this->db->manipulateF(
                'UPDATE frm_threads SET is_sticky = %s WHERE thr_pk = %s',
                ['integer', 'integer'],
                [0, $this->id]
            );

            $this->is_sticky = false;
            return true;
        }

        return false;
    }

    public function close(): void
    {
        if ($this->id && !$this->is_closed) {
            $this->db->manipulateF(
                'UPDATE frm_threads SET is_closed = %s WHERE thr_pk = %s',
                ['integer', 'integer'],
                [1, $this->id]
            );
            $this->is_closed = true;
        }
    }

    public function reopen(): void
    {
        if ($this->id && $this->is_closed) {
            $this->db->manipulateF(
                'UPDATE frm_threads SET is_closed = %s WHERE thr_pk = %s',
                ['integer', 'integer'],
                [0, $this->id]
            );

            $this->is_closed = false;
        }
    }

    public function getAverageRating(): float
    {
        return $this->average_rating;
    }

    public function setAverageRating(float $average_rating): void
    {
        $this->average_rating = $average_rating;
    }

    public function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setForumId(int $a_forum_id): void
    {
        $this->forum_id = $a_forum_id;
    }

    public function getForumId(): int
    {
        return $this->forum_id;
    }

    public function setDisplayUserId(int $a_user_id): void
    {
        $this->display_user_id = $a_user_id;
    }

    public function getDisplayUserId(): int
    {
        return $this->display_user_id;
    }

    public function setUserAlias(?string $a_user_alias): void
    {
        $this->user_alias = $a_user_alias;
    }

    public function getUserAlias(): ?string
    {
        return $this->user_alias;
    }

    public function setSubject(string $a_subject): void
    {
        $this->subject = $a_subject;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setCreateDate(?string $a_createdate): void
    {
        $this->createdate = $a_createdate;
    }

    public function getCreateDate(): ?string
    {
        return $this->createdate;
    }

    public function setChangeDate(?string $a_changedate): void
    {
        $this->changedate = $a_changedate;
    }

    public function getChangeDate(): ?string
    {
        return $this->changedate;
    }

    public function setImportName(?string $a_import_name): void
    {
        $this->import_name = $a_import_name;
    }

    public function getImportName(): ?string
    {
        return $this->import_name;
    }

    public function setLastPostString(?string $a_last_post): void
    {
        $this->last_post_string = $a_last_post;
    }

    public function getLastPostString(): ?string
    {
        return $this->last_post_string;
    }

    public function setVisits(int $a_visits): void
    {
        $this->visits = $a_visits;
    }

    public function getVisits(): int
    {
        return $this->visits;
    }

    public function setSticky(bool $a_sticky): void
    {
        $this->is_sticky = $a_sticky;
    }

    public function isSticky(): bool
    {
        return $this->is_sticky;
    }

    public function setClosed(bool $a_closed): void
    {
        $this->is_closed = $a_closed;
    }

    public function isClosed(): bool
    {
        return $this->is_closed;
    }

    public function setOrderField(string $a_order_field): void
    {
        $this->orderField = $a_order_field;
    }

    public function getOrderField(): string
    {
        return $this->orderField;
    }

    public function getFrmObjId(): int
    {
        return $this->frm_obj_id;
    }

    public function setThrAuthorId(int $thr_author_id): void
    {
        $this->thr_author_id = $thr_author_id;
    }

    public function getThrAuthorId(): int
    {
        return $this->thr_author_id;
    }

    public static function lookupTitle(int $a_topic_id): string
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT thr_subject FROM frm_threads WHERE thr_pk = %s',
            ['integer'],
            [$a_topic_id]
        );

        if ($row = $ilDB->fetchObject($res)) {
            return (string) $row->thr_subject;
        }

        return '';
    }

    public function updateThreadTitle(): void
    {
        $this->db->update(
            'frm_threads',
            ['thr_subject' => ['text', $this->getSubject()]],
            ['thr_pk' => ['integer', $this->getId()]]
        );

        try {
            $first_node = $this->getFirstVisiblePostNode();
            $first_node->setSubject($this->getSubject());
            $first_node->update();
        } catch (OutOfBoundsException) {
        }
    }

    public function setNumPosts(int $a_num_posts): ilForumTopic
    {
        $this->num_posts = $a_num_posts;
        return $this;
    }

    public function getNumPosts(): int
    {
        return $this->num_posts;
    }

    public function setNumNewPosts(int $num_new_posts): ilForumTopic
    {
        $this->num_new_posts = $num_new_posts;
        return $this;
    }

    public function getNumNewPosts(): int
    {
        return $this->num_new_posts;
    }

    public function setNumUnreadPosts(int $num_unread_posts): ilForumTopic
    {
        $this->num_unread_posts = $num_unread_posts;
        return $this;
    }

    public function getNumUnreadPosts(): int
    {
        return $this->num_unread_posts;
    }

    public function setUserNotificationEnabled(bool $status): ilForumTopic
    {
        $this->user_notification_enabled = $status;
        return $this;
    }

    public function isUserNotificationEnabled(): bool
    {
        return $this->user_notification_enabled;
    }

    public function setOrderDirection(string $direction): ilForumTopic
    {
        if (!in_array(strtoupper($direction), self::$possibleOrderDirections, true)) {
            $direction = current(self::$possibleOrderDirections);
        }

        $this->orderDirection = $direction;
        return $this;
    }

    public function getOrderDirection(): string
    {
        return $this->orderDirection;
    }

    public static function lookupForumIdByTopicId(int $a_topic_id): int
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT thr_top_fk FROM frm_threads WHERE thr_pk = %s',
            ['integer'],
            [$a_topic_id]
        );

        $row = $ilDB->fetchAssoc($res);

        return (int) $row['thr_top_fk'];
    }

    public function updateMergedThread(): void
    {
        $this->db->update(
            'frm_threads',
            [
                'thr_num_posts' => ['integer', $this->getNumPosts()],
                'visits' => ['integer', $this->getVisits()],
                'thr_last_post' => ['text', $this->getLastPostString()],
                'thr_subject' => ['text', $this->getSubject()]
            ],
            ['thr_pk' => ['integer', $this->getId()]]
        );
    }

    public static function lookupCreationDate(int $thread_id): ?string
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT thr_date FROM frm_threads WHERE thr_pk = %s',
            ['integer'],
            [$thread_id]
        );

        $date = null;
        $row = $ilDB->fetchAssoc($res);
        if (is_array($row)) {
            $date = $row['thr_date'];
        }

        return $date;
    }

    public function getLastPostForThreadOverview(): ?ilForumPost
    {
        return $this->last_post;
    }

    public function setLastPostForThreadOverview(ilForumPost $post): void
    {
        $this->last_post = $post;
    }
}
