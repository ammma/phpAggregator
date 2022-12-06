<?php

namespace App\Services\Aggregator\Callbacks;

class EveryNumOfRecordsCallback
{
    /**
     * @var int
     */
    protected $everyNumRecords;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @param int $everyNumRecords
     * @param callable $callback
     */
    public function __construct(int $everyNumRecords, callable $callback) {
        $this->everyNumRecords = $everyNumRecords;
        $this->callback = $callback;
    }

    /**
     * @return int
     */
    public function getEveryNumRecords(): int
    {
        return $this->everyNumRecords;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }
}