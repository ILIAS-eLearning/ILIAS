<?php declare(strict_types=1);

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
 * Class ilMailTemplateRepository
 */
class ilMailTemplateRepository
{
    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db = null)
    {
        global $DIC;
        $this->db = $db ?? $DIC->database();
    }

    /**
     * @return ilMailTemplate[]
     */
    public function getAll() : array
    {
        $templates = [];

        $res = $this->db->query('SELECT * FROM mail_man_tpl');
        while ($row = $this->db->fetchAssoc($res)) {
            $template = new ilMailTemplate($row);
            $templates[] = $template;
        }

        return $templates;
    }

    /**
     * @param int $templateId
     * @return ilMailTemplate
     */
    public function findById(int $templateId) : ilMailTemplate
    {
        $res = $this->db->queryF(
            'SELECT * FROM mail_man_tpl WHERE tpl_id  = %s',
            ['integer'],
            [$templateId]
        );

        if (1 === $this->db->numRows($res)) {
            $row = $this->db->fetchAssoc($res);
            return new ilMailTemplate($row);
        }

        throw new OutOfBoundsException(sprintf("Could not find template by id: %s", $templateId));
    }

    /**
     * @param string $contextId
     * @return ilMailTemplate[]
     */
    public function findByContextId(string $contextId) : array
    {
        return array_filter($this->getAll(), static function (ilMailTemplate $template) use ($contextId) : bool {
            return $contextId === $template->getContext();
        });
    }

    /**
     * @param int[] $templateIds
     */
    public function deleteByIds(array $templateIds) : void
    {
        if (count($templateIds) > 0) {
            $this->db->manipulate(
                'DELETE FROM mail_man_tpl WHERE ' . $this->db->in('tpl_id', $templateIds, false, 'integer')
            );
        }
    }

    public function store(ilMailTemplate $template) : void
    {
        if ($template->getTplId() > 0) {
            $this->db->update(
                'mail_man_tpl',
                [
                    'title' => ['text', $template->getTitle()],
                    'context' => ['text', $template->getContext()],
                    'lang' => ['text', $template->getLang()],
                    'm_subject' => ['text', $template->getSubject()],
                    'm_message' => ['text', $template->getMessage()],
                    'is_default' => ['text', $template->isDefault()],
                ],
                [
                    'tpl_id' => ['integer', $template->getTplId()],
                ]
            );
        } else {
            $nextId = $this->db->nextId('mail_man_tpl');
            $this->db->insert('mail_man_tpl', [
                'tpl_id' => ['integer', $nextId],
                'title' => ['text', $template->getTitle()],
                'context' => ['text', $template->getContext()],
                'lang' => ['text', $template->getLang()],
                'm_subject' => ['text', $template->getSubject()],
                'm_message' => ['text', $template->getMessage()],
                'is_default' => ['integer', $template->isDefault()],
            ]);
            $template->setTplId($nextId);
        }
    }
}
