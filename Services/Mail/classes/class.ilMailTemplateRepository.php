<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTemplateRepository
 */
class ilMailTemplateRepository
{
    /** @var \ilDBInterface */
    protected $db;

    /**
     * ilMailTemplateRepository constructor.
     * @param ilDBInterface|null $db
     */
    public function __construct(\ilDBInterface $db = null)
    {
        global $DIC;

        if (null === $db) {
            $db = $DIC->database();
        }
        $this->db = $db;
    }

    /**
     * @return \ilMailTemplate[]
     */
    public function getAll() : array
    {
        $templates = [];

        $res = $this->db->query('SELECT * FROM mail_man_tpl');
        while ($row = $this->db->fetchAssoc($res)) {
            $template = new \ilMailTemplate($row);
            $templates[] = $template;
        }

        return $templates;
    }

    /**
     * @param int $templateId
     * @return \ilMailTemplate
     * @throws \OutOfBoundsException
     */
    public function findById(int $templateId) : \ilMailTemplate
    {
        $res = $this->db->queryF(
            'SELECT * FROM mail_man_tpl WHERE tpl_id  = %s',
            ['integer'],
            [$templateId]
        );

        if (1 === (int) $this->db->numRows($res)) {
            $row = $this->db->fetchAssoc($res);
            return new \ilMailTemplate($row);
        }

        throw new \OutOfBoundsException(sprintf("Could not find template by id: %s", $templateId));
    }

    /**
     * @param string $contextId
     * @return \ilMailTemplate[]
     */
    public function findByContextId(string $contextId) : array
    {
        return array_filter($this->getAll(), function (\ilMailTemplate $template) use ($contextId) {
            return $contextId === $template->getContext();
        });
    }

    /**
     * @param int[] $templateIds
     */
    public function deleteByIds(array $templateIds)
    {
        if (count($templateIds) > 0) {
            $this->db->manipulate(
                '
				DELETE FROM mail_man_tpl WHERE ' .
                $this->db->in('tpl_id', $templateIds, false, 'integer')
            );
        }
    }

    /**
     * @param \ilMailTemplate $template
     */
    public function store(\ilMailTemplate $template)
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
