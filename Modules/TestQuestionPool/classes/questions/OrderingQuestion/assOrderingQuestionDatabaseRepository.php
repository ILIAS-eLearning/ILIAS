<?php declare(strict_types=1);

namespace ILIAS\TA\Questions\Ordering;

/**
 * repository for assOrderingQuestion (the answer elements within, at least...)
 */
class assOrderingQuestionDatabaseRepository
{
    const TABLE_NAME_BASE = 'qpl_questions';
    const TABLE_NAME_QUESTIONS = 'qpl_qst_ordering';
    const TABLE_NAME_ANSWERS = 'qpl_a_ordering';

    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function getOrderingList(int $question_id) : \ilAssOrderingElementList
    {
        return $this->buildOrderingList($question_id);
    }

    public function updateOrderingList(\ilAssOrderingElementList $list) : void
    {
        $atom_query = $this->db->buildAtomQuery();
        $atom_query->addTableLock(self::TABLE_NAME_ANSWERS);
        $atom_query->addTableLock(self::TABLE_NAME_ANSWERS . '_seq');

        $atom_query->addQueryCallable(
            function (\ilDBInterface $db) use ($list) {
                $this->deleteOrderingElements($list->getQuestionId());
                foreach ($list->getElements() as $order_element) {
                    $this->insertOrderingElement($order_element, $list->getQuestionId());
                }
            }
        );
        $atom_query->run();
    }
    
    protected function buildOrderingList(
        int $question_id,
        array $elements = []
    ) : \ilAssOrderingElementList {
        $elements = $this->getOrderingElementsForList($question_id);
        return new \ilAssOrderingElementList($question_id, $elements);
    }

    /**
     * @return \ilAssOrderingElement[]
     */
    protected function getOrderingElementsForList(int $question_id) : array
    {
        $query = 'SELECT' . PHP_EOL
            . 'answer_id, answertext, solution_key, random_id, depth, position' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME_ANSWERS . PHP_EOL
            . 'WHERE question_fi=' . $question_id . PHP_EOL
            . 'ORDER BY position ASC';

        $elements = [];
        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $elements[] = $this->buildOrderingElement(
                (int) $row['answer_id'],
                (int) $row['random_id'],
                (int) $row['solution_key'],
                (int) $row['position'],
                (int) $row['depth'],
                (string) $row['answertext']
            );
        }
        return $elements;
    }

    protected function deleteOrderingElements(int $question_id) : void
    {
        $query = 'DELETE FROM ' . self::TABLE_NAME_ANSWERS . PHP_EOL
            . 'WHERE question_fi = ' . $question_id;
        $this->db->manipulate($query);
    }

    protected function insertOrderingElement(\ilAssOrderingElement $order_element, int $question_id) : void
    {
        $next_id = $this->db->nextId(self::TABLE_NAME_ANSWERS);
        $values = array(
            'answer_id' => ['integer', $next_id],
            'question_fi' => ['integer', $question_id],
            'answertext' => ['text', $order_element->getContent()],
            'solution_key' => ['integer', $order_element->getSolutionIdentifier()],
            'random_id' => ['integer', $order_element->getRandomIdentifier()],
            'position' => ['integer', $order_element->getPosition()],
            'depth' => ['integer', $order_element->getIndentation()],
            'tstamp' => ['integer', $this->getTime()]
        );
        $this->db->insert(self::TABLE_NAME_ANSWERS, $values);
    }

    protected function getTime()
    {
        return time();
    }

    protected function buildOrderingElement(
        int $answer_id,
        int $random_identifier,
        int $solution_identifier,
        int $position,
        int $indentation,
        string $content
    ) : \ilAssOrderingElement {
        return (new \ilAssOrderingElement($answer_id))
            ->withRandomIdentifier($random_identifier)
            ->withSolutionIdentifier($solution_identifier)
            ->withPosition($position)
            ->withIndentation($indentation)
            ->withContent($content);
    }
}
