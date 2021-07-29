<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilExternalAuthUserAttributeMapping
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilExternalAuthUserAttributeMapping implements ArrayAccess, Countable, Iterator
{
    protected ilDBInterface $db;
    protected string $authMode;
    protected int $authSourceId;
    /** @var ilExternalAuthUserAttributeMappingRule[] */
    protected array $mapping = [];

    public function __construct(string $authMode, int $authSourceId = 0)
    {
        $this->db = $GLOBALS['DIC']->database();

        $this->setAuthMode($authMode);
        $this->setAuthSourceId($authSourceId);

        $this->read();
    }

    public function getAuthSourceId() : int
    {
        return $this->authSourceId;
    }

    public function setAuthSourceId(int $authSourceId) : void
    {
        $this->authSourceId = $authSourceId;
    }

    public function getAuthMode() : string
    {
        return $this->authMode;
    }

    public function setAuthMode(string $authMode) : void
    {
        $this->authMode = $authMode;
    }

    public function getEmptyRule() : ilExternalAuthUserAttributeMappingRule
    {
        return new ilExternalAuthUserAttributeMappingRule();
    }

    public function offsetExists($offset) : bool
    {
        return isset($this->mapping[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->mapping[$offset] : null;
    }

    public function offsetSet($offset, $value) : void
    {
        if (is_null($offset)) {
            $this->mapping[] = $value;
        } else {
            $this->mapping[$offset] = $value;
        }
    }

    public function offsetUnset($offset) : void
    {
        unset($this->mapping[$offset]);
    }

    public function count() : int
    {
        return count($this->mapping);
    }

    public function current() : ilExternalAuthUserAttributeMappingRule
    {
        return current($this->mapping);
    }

    public function next() : void
    {
        next($this->mapping);
    }

    public function key()
    {
        return key($this->mapping);
    }

    public function valid()
    {
        return current($this->mapping);
    }

    public function rewind() : void
    {
        reset($this->mapping);
    }

    protected function read() : void
    {
        $this->mapping = [];

        $res = $this->db->queryF(
            'SELECT * FROM auth_ext_attr_mapping WHERE auth_mode = %s AND auth_src_id = %s',
            ['text', 'integer'],
            [$this->getAuthMode(), $this->getAuthSourceId()]
        );
        while ($row = $this->db->fetchAssoc($res)) {
            $rule = $this->getEmptyRule();
            $rule->setAttribute($row['attribute']);
            $rule->setExternalAttribute($row['ext_attribute']);
            $rule->updateAutomatically((bool) $row['update_automatically']);

            $this->mapping[$rule->getAttribute()] = $rule;
        }
    }

    public function save() : void
    {
        foreach ($this->mapping as $rule) {
            $this->db->replace(
                'auth_ext_attr_mapping',
                [
                    'auth_mode' => ['text', $this->getAuthMode()],
                    'auth_src_id' => ['integer', $this->getAuthSourceId()],
                    'attribute' => ['text', $rule->getAttribute()]
                ],
                [
                    'ext_attribute' => ['text', $rule->getExternalAttribute()],
                    'update_automatically' => ['integer', (int) $rule->isAutomaticallyUpdated()]
                ]
            );
        }
    }

    public function delete() : void
    {
        $this->mapping = [];
        $this->db->manipulateF(
            'DELETE FROM auth_ext_attr_mapping WHERE auth_mode = %s AND auth_src_id = %s',
            ['text', 'integer'],
            [$this->getAuthMode(), $this->getAuthSourceId()]
        );
    }
}
