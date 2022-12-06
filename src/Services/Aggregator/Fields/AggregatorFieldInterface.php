<?php
namespace App\Services\Aggregator\Fields;

interface AggregatorFieldInterface
{
    /**
     * @return string
     */
    public function getResultKey(): string;

    /**
     * @return string|callable
     */
    public function getKey();

    /**
     * @param array $dataSet
     *
     * @return mixed
     */
    public function getValue(array $dataSet);
}