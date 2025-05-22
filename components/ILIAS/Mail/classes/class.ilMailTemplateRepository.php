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

class ilMailTemplateRepository
{
    protected ilDBInterface $db;

    public function __construct(?ilDBInterface $db = null)
    {
        global $DIC;
        $this->db = $db ?? $DIC->database();
    }

    /**
     * @return list<ilMailTemplate>
     */
    public function getAll(): array
    {
        $templates = [];

        $res = $this->db->query('SELECT * FROM mail_man_tpl');
        while ($row = $this->db->fetchAssoc($res)) {
            $template = new ilMailTemplate($row);
            $templates[] = $template;
        }

        return $templates;
    }

    public function findById(int $template_id): ilMailTemplate
    {
        $res = $this->db->queryF(
            'SELECT * FROM mail_man_tpl WHERE tpl_id  = %s',
            [ilDBConstants::T_INTEGER],
            [$template_id]
        );

        if ($this->db->numRows($res) === 1) {
            $row = $this->db->fetchAssoc($res);
            return new ilMailTemplate($row);
        }

        throw new OutOfBoundsException(sprintf('Could not find template by id: %s', $template_id));
    }

    /**
     * @return list<ilMailTemplate>
     */
    public function findByContextId(string $context_id): array
    {
        return array_values(
            array_filter(
                $this->getAll(),
                static fn(ilMailTemplate $template): bool => $context_id === $template->getContext()
            )
        );
    }

    /**
     * @param list<int> $template_ids
     */
    public function deleteByIds(array $template_ids): void
    {
        if ($template_ids !== []) {
            $this->db->manipulate(
                'DELETE FROM mail_man_tpl WHERE ' . $this->db->in('tpl_id', $template_ids, false, ilDBConstants::T_INTEGER)
            );
        }
    }

    public function store(ilMailTemplate $template): void
    {
        if ($template->getTplId() > 0) {
            $this->db->update(
                'mail_man_tpl',
                [
                    'title' => [ilDBConstants::T_TEXT, $template->getTitle()],
                    'context' => [ilDBConstants::T_TEXT, $template->getContext()],
                    'lang' => [ilDBConstants::T_TEXT, $template->getLang()],
                    'm_subject' => [ilDBConstants::T_TEXT, $template->getSubject()],
                    'm_message' => [ilDBConstants::T_TEXT, $template->getMessage()],
                    'is_default' => [ilDBConstants::T_INTEGER, $template->isDefault()],
                ],
                [
                    'tpl_id' => [ilDBConstants::T_INTEGER, $template->getTplId()],
                ]
            );
        } else {
            $next_id = $this->db->nextId('mail_man_tpl');
            $this->db->insert('mail_man_tpl', [
                'tpl_id' => [ilDBConstants::T_INTEGER, $next_id],
                'title' => [ilDBConstants::T_TEXT, $template->getTitle()],
                'context' => [ilDBConstants::T_TEXT, $template->getContext()],
                'lang' => [ilDBConstants::T_TEXT, $template->getLang()],
                'm_subject' => [ilDBConstants::T_TEXT, $template->getSubject()],
                'm_message' => [ilDBConstants::T_TEXT, $template->getMessage()],
                'is_default' => [ilDBConstants::T_INTEGER, $template->isDefault()],
            ]);
            $template->setTplId($next_id);
        }
    }
}
