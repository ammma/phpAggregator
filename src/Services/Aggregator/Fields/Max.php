<?php
namespace App\Services\Aggregator\Fields;

class Max extends AbstractField implements AggregatorFieldInterface
{
    /**
     * @param array $dataSet
     *
     * @return float|null
     */
    function getValue(array $dataSet)
    {
        $values = array_column($dataSet, $this->getKey());

        if (empty($values)) {
            return null;
        }

        return max($values);
    }
}