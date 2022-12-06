<?php
namespace App\Services\Aggregator\Fields;

class Sum extends AbstractField implements AggregatorFieldInterface
{
    /**
     * @param array $dataSet
     *
     * @return float|int|null
     */
    function getValue(array $dataSet)
    {
        $values = array_column($dataSet, $this->getKey());

        if (empty($values)) {
            return null;
        }

        return array_sum($values);
    }
}