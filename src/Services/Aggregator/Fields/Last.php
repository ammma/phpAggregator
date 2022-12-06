<?php
namespace App\Services\Aggregator\Fields;

class Last extends AbstractField implements AggregatorFieldInterface
{
    /**
     * @param array $dataSet
     *
     * @return mixed
     */
    function getValue(array $dataSet)
    {
        foreach (array_reverse($dataSet) as $item) {
            if (isset($item[$this->getKey()]) && $item[$this->getKey()] !== null) {
                return $item[$this->getKey()];
            }
        }

        return null;
    }
}