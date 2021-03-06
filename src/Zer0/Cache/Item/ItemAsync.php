<?php

namespace Zer0\Cache\Item;

use PHPDaemon\Structures\StackCallbacks;
use Zer0\Cache\Pools\BaseAsync;
use Zer0\Queue\TaskAbstract;

/**
 * Class ItemAsync
 * @package Zer0\Cache\Item
 */
class ItemAsync extends ItemAbstract
{
    /**
     * @var BaseAsync
     */
    protected $pool;

    /**
     * @var StackCallbacks
     */
    protected $onSet;

    /**
     * Item constructor.
     * @param string $key
     * @param BaseAsync $pool
     */
    public function __construct(string $key, BaseAsync $pool)
    {
        $this->key = $key;
        $this->pool = $pool;
        $this->onSet = new StackCallbacks();
    }

    /**
     * @param $value
     * @return ItemAbstract
     */
    public function set($value): ItemAbstract
    {
        parent::set($value);
        $this->onSet->executeAll($this);
        return $this;
    }

    /**
     * @param callable $cb
     * @return void
     */
    public function get($cb)
    {
        if ($this->hasValue !== null) {
            $cb($this);
            return;
        }
        $this->pool->getValueByKey($this->key, function ($value, $hasValue) use ($cb) {
            $this->value = $value;
            $this->hasValue = $hasValue;
            if ($hasValue === null && $this->callback !== null) {
                call_user_func($this->callback, $this);
                $this->onSet->push($cb);
            } else {
                $cb($this);
            }
        });
    }
    /**
     * @param TaskAbstract $task
     */
    public function setCallbackTask(TaskAbstract $task, \Zer0\Queue\Pools\BaseAsync $queue, int $timeout = 30): self
    {
        return $this->setCallback(function (Item $item) use ($queue, $timeout) {
            try {
                $queue->enqueueWait(
                    $task,
                    $timeout,
                    function (?TaskAbstract $task) {
                        $this->get(function() {
                            $this->onSet->executeAll($this);
                        });
                    }
                );
            } catch (\Zer0\Queue\Exceptions\WaitTimeoutException $e) {
            }
        });
    }
}
