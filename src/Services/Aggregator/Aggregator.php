<?php

namespace App\Services\Aggregator;

use App\Services\Aggregator\Callbacks\EveryNumOfRecordsCallback;
use App\Services\Aggregator\Fields\AggregatorFieldInterface;

/**
 * @method Aggregator count($resultKey = 'count') Get a count # of records.
 * @method Aggregator countIf($filter, $resultKey = null) Count values if key is present (string $filter) or if Closure returns true.
 * @method Aggregator sum($keyToSum, $resultKey = null) Like sum(keyToSum) as resultKey in sql.
 * @method Aggregator avg($keyToAvg, $resultKey = null) Like avg(keyToAvg) as resultKey in sql.
 * @method Aggregator first(string $key, ?string $resultKey = null) Return the first NON-NULL value passed on a data key
 * @method Aggregator last(string $key, ?string $resultKey = null) Return the last NON-NULL value passed on a data key
 * @method Aggregator min($key, $resultKey = null) Get the smallest value passed on a data key.
 * @method Aggregator max($key, $resultKey = null) Get the largest value passed on a data key.
 * @method Aggregator distinct($key, $resultKey = null) Return a php array of unique values.
 */
class Aggregator
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array<AggregatorFieldInterface>
     */
    protected $selectFields = [];

    /**
     * @var array<EveryNumOfRecordsCallback>
     */
    protected $afterRecordAddCallbacks = [];

    /**
     * @var array<string>
     */
    protected $groupBy = [];

    /**
     * @return Aggregator
     */
    public static function create(): Aggregator
    {
        return new self();
    }

    /**
     * Return some or all of the final/current result of the aggregation pipeline.
     *
     * @param string|null $key
     * @param $default
     *
     * @return mixed
     */
    public function get(string $key = null, $default = null)
    {
        $result = [];
        $groupedData = $this->groupData();
        $selectFields = $this->selectFields;

        if (!empty($key)) {
            $selectFields = array_filter(
                $selectFields,
                function ($obj) use ($key) {
                    return $obj->getResultKey() === $key;
                }
            );

            if (empty($selectFields)) {
                return $default;
            }
        }

        foreach ($groupedData as $groupKey => $groupData) {
            $result[$groupKey] = [];

            foreach ($selectFields as $selectField) {
                $value = $selectField->getValue($groupData);

                if ($key === $selectField->getResultKey() && $value === null) {
                    $value = $default;
                }

                $result[$groupKey][$selectField->getResultKey()] = $value;
            }
        }

        if (empty($this->groupBy)) {
            return empty($key) ? $result[0] : $result[0][$key];
        }

        return $result;
    }

    /**
     * Add data to aggregator to be processed
     *
     * @param array $data
     *
     * @return $this
     */
    public function add(array $data): self
    {
        $this->data[] = $data;

        $this->executeCallbacks();

        return $this;
    }

    /**
     * Control how results will be grouped.
     *
     * @param $key
     *
     * @return $this
     */
    public function groupBy($key): self
    {
        $this->groupBy[] = $key;

        $this->groupBy = array_unique($this->groupBy);

        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return $this|null
     */
    public function __call($name, $arguments)
    {
        $className = "App\Services\Aggregator\Fields\\" . ucwords($name);

        if (class_exists($className)) {
            $this->addSelectField(new $className($arguments[0], $arguments[1]));

            return $this;
        }

        return null;
    }

    /**
     * Adds field to be returned with results.
     *
     * @param AggregatorFieldInterface $aggregatorField
     *
     * @return $this
     */
    public function addSelectField(AggregatorFieldInterface $aggregatorField): self
    {
        $this->selectFields[] = $aggregatorField;

        return $this;
    }

    /**
     * For every N number of records processed, fire a callback.
     *
     * @param int $numRecords
     * @param callable $callback
     *
     * @return $this
     */
    public function every(int $numRecords, callable $callback): self
    {
        $this->afterRecordAddCallbacks[] = new EveryNumOfRecordsCallback($numRecords, $callback);

        return $this;
    }

    /**
     * @return bool
     */
    protected function executeCallbacks(): bool
    {
        if (empty($this->afterRecordAddCallbacks)) {
            return false;
        }

        $numRecords = count($this->data);

        foreach ($this->afterRecordAddCallbacks as $afterRecordAddCallback) {
            if ($numRecords % $afterRecordAddCallback->getEveryNumRecords() !== 0) {
                continue;
            }

            call_user_func($afterRecordAddCallback->getCallback());
        }

        return true;
    }

    /**
     * @return array[]
     */
    protected function groupData(): array
    {
        if (empty($this->groupBy)) {
            return [$this->data];
        }

        $groupedData = [];

        foreach ($this->data as $line) {
            $key = '';

            foreach ($this->groupBy as $groupByField) {
                $key .= $line[$groupByField];
            }

            $groupedData[$key][] = $line;
        }

        return $groupedData;
    }
}