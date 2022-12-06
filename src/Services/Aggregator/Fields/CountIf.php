<?php
namespace App\Services\Aggregator\Fields;

class CountIf extends AbstractField implements AggregatorFieldInterface
{
    /**
     * @param array $dataSet
     *
     * @return int
     */
    function getValue(array $dataSet)
    {
        $countCallback = is_callable($this->getKey()) ? $this->getKey() : function ($row) {
            return isset($row[$this->getKey()]);
        };
        return count(array_filter($dataSet, $countCallback));
    }
}