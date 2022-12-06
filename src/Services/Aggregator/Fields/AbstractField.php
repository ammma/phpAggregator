<?php
namespace App\Services\Aggregator\Fields;

abstract class AbstractField
{
    /**
     * @var string|callable
     */
    protected $key;

    /**
     * @var string|null
     */
    protected $resultKey;

    /**
     * @param string|callable $key
     * @param string|null $resultKey
     */
    public function __construct($key, ?string $resultKey = null)
    {
        $this->key = $key;

        $this->resultKey = $resultKey;
    }

    /**
     * @return string
     */
    public function getResultKey(): string
    {
        return $this->resultKey ?? $this->key;
    }

    /**
     * @return string|callable
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param array $dataSet
     *
     * @return mixed
     */
    abstract function getValue(array $dataSet);
}