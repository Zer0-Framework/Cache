<?php

namespace Zer0\Cache\Item;

use Zer0\Queue\TaskAbstract;

/**
 * Class ItemAbstract
 * @package Zer0\Cache\Item
 */
abstract class ItemAbstract
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var null|mixed
     */
    public $value;

    /**
     * @var bool
     */
    public $hasValue;

    /**
     * @var int
     */
    public $ttl;

    /**
     * @var
     */
    public $addTags = [];

    /**
     * @var array
     */
    public $removeTags = [];


    /**
     * @var callable
     */
    public $callback;

    /**
     * @param $value
     * @return Item
     */
    public function set($value): self
    {
        $this->value = $value;
        $this->hasValue = true;
        return $this;
    }

    /**
     *
     */
    public function reset(): self
    {
        $this->value = null;
        $this->hasValue = null;
        return $this;
    }

    /**
     * @param callable $cb
     * @return self
     */
    public function setCallback(callable $cb): self
    {
        $this->callback = $cb;
        return $this;
    }

    /**
     * @param int $seconds
     * @return Item
     */
    public function expires(int $seconds = 0): self
    {
        $this->ttl = $seconds;
        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function expiresAt(string $date): self
    {
        $ttl = strtotime($date) - time();
        if ($ttl <= 0) {
            throw new \InvalidArgumentException(sprintf('Date (%s) cannot be in the past', $date));
        }
        $this->ttl = $ttl;
        return $this;
    }

    /**
     * @deprecated
     * @alias expires
     * @param int $seconds
     * @return Item
     */
    public function expiresAfter($seconds = 0): self
    {
        return $this->expires($seconds);
    }

    /**
     * @param array $tags
     * @return ItemAbstract
     */
    public function addTags(array $tags): self
    {
        $this->addTags = array_merge($this->addTags, $tags);
        return $this;
    }

    /**
     * @param string $tag
     * @return ItemAbstract
     */
    public function addTag(string $tag): self
    {
        $this->addTags[] = $tag;
        return $this;
    }

    /**
     * @param array $tags
     * @return ItemAbstract
     */
    public function removeTags(array $tags)
    {
        $this->removeTags = array_merge($this->removeTags, $tags);
        return $this;
    }

    /**
     * @param string $tag
     * @return ItemAbstract
     */
    public function removeTag(string $tag)
    {
        $this->removeTags[] = $tag;
        return $this;
    }
}
