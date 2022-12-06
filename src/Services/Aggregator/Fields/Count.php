<?php
namespace App\Services\Aggregator\Fields;

class Count extends AbstractField implements AggregatorFieldInterface
{
    /**
     * @param array $dataSet
     *
     * @return int
     */
    function getValue(array $dataSet)
    {
        return count($dataSet);
    }
}