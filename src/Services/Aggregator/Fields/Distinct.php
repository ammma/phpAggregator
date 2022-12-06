<?php
namespace App\Services\Aggregator\Fields;

class Distinct extends AbstractField implements AggregatorFieldInterface
{
    /**
     * @param array $dataSet
     *
     * @return array|null
     */
    function getValue(array $dataSet)
    {
        $values = array_column($dataSet, $this->getKey());

        if (empty($values)) {
            return null;
        }

        return array_unique($values);
    }
}