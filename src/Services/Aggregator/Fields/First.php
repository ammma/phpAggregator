<?php
namespace App\Services\Aggregator\Fields;

class First extends AbstractField implements AggregatorFieldInterface
{
    /**
     * @param array $dataSet
     *
     * @return mixed
     */
    function getValue(array $dataSet)
    {
        foreach ($dataSet as $item) {
            if (isset($item[$this->getKey()]) && $item[$this->getKey()] !== null) {
                return $item[$this->getKey()];
            }
        }

        return null;
    }
}