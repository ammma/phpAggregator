<?php
namespace App\Services\Aggregator\Fields;

class Avg extends AbstractField implements AggregatorFieldInterface
{
    /**
     * @var int
     */
    protected const ROUND_PRECISION = 2;

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

        return round(array_sum($values) / count($dataSet), static::ROUND_PRECISION);
    }
}