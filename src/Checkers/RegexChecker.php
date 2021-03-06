<?php

namespace BlockListCheck\Checkers;

use BlockListCheck\Contracts\BlocklistChecker;
use BlockListCheck\Exceptions\BlockListCheckException;

class RegexChecker implements BlocklistChecker
{
    use HasPatterns, HasFields;

    /**
     * @param array $patterns
     * @param array $fields
     */
    public function __construct(array $patterns = [], array $fields = [])
    {
        $this->patterns = $patterns;
        $this->fields   = $fields;
    }


    /**
     * @inheritDoc
     */
    public function check(mixed $entity): bool
    {
        foreach ($this->fields as $field) {
            foreach ($this->patterns as $pattern) {
                if (preg_match($pattern, (string) $this->getEntityValue($entity, $field))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get field value from entity.
     *
     * @param mixed $entity
     * @param string $field
     *
     * @return mixed
     * @throws BlockListCheckException
     */
    protected function getEntityValue(mixed $entity, string $field): mixed
    {
        if (is_array($entity)) {
            return $this->getArrayValue($entity, $field);
        } elseif (is_object($entity)) {
            return $this->getObjectValue($entity, $field);
        }

        throw new BlockListCheckException("Field [$field] value not found.");
    }

    /**
     * Get field value from array.
     *
     * @param mixed $entity
     * @param string $field
     *
     * @return mixed
     * @throws BlockListCheckException
     */
    protected function getArrayValue(array $entity, string $field): mixed
    {
        if (array_key_exists($field, $entity)) {
            return $entity[ $field ];
        }

        throw new BlockListCheckException("Field [$field] value not found.");
    }

    /**
     * Get field value from object.
     *
     * @param object $entity
     * @param string $field
     *
     * @return mixed
     * @throws BlockListCheckException
     */
    protected function getObjectValue(object $entity, string $field): mixed
    {
        if (property_exists($entity, $field)) {
            return $entity->{$field};
        } elseif (method_exists($entity, 'blocklistCheckValue')) {
            return $entity->blocklistCheckValue($field);
        } elseif (method_exists($entity, 'getAttribute')) {
            return $entity->getAttribute($field);
        }

        throw new BlockListCheckException("Field [$field] value not found.");
    }
}
