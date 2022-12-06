<?php declare(strict_types=1);

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use App\Services\Aggregator\Aggregator as AggregatorService;

final class AggregatorTest extends TestCase
{
    public function testLast(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->last('some_key')
            ->last('some_other_key', 'cust_key')
            ->add(['some_key' => 10])
            ->add(['some_key' => 100])
            ->add(['some_key' => null])
            ->add(['some_other_key' => 1]);

        // Assert
        Assert::assertEquals(100, $agg->get('some_key'));
        Assert::assertEquals(1, $agg->get('cust_key'));
    }

    public function testLastGroupBy(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->last('some_key')
            ->groupBy('group')
            ->add(['some_key' => 1, 'group' => 1])
            ->add(['some_key' => 10, 'group' => 1])
            ->add(['some_key' => null, 'group' => 2])
            ->add(['some_key' => 10, 'group' => 2])
            ->add(['some_key' => 100, 'group' => 2]);

        // Assert
        Assert::assertEquals(10, $agg->get()[1]['some_key']);
        Assert::assertEquals(100, $agg->get()[2]['some_key']);
    }

    public function testFirst(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->first('some_key')
            ->first('some_other_key', 'cust_key')
            ->add(['some_other_key' => 1])
            ->add(['some_key' => null])
            ->add(['some_key' => 10]);

        // Assert
        Assert::assertEquals(10, $agg->get('some_key'));
        Assert::assertEquals(1, $agg->get('cust_key'));
    }

    public function testFirstGroupBy(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->first('some_key')
            ->groupBy('group')
            ->add(['some_key' => 1, 'group' => 1])
            ->add(['some_key' => null, 'group' => 2])
            ->add(['some_key' => 10, 'group' => 2]);

        // Assert
        Assert::assertEquals(1, $agg->get()[1]['some_key']);
        Assert::assertEquals(10, $agg->get()[2]['some_key']);
    }

    public function testReturnSpecificKey(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->countIf('score')
            ->countIf(function ($row) {
                return $row['score'] >= .8;
            }, 'good_score');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertEquals(5, $agg->get('score'));
    }

    public function testReturnCustomKeyAndDefault(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg
            ->sum('score', 'sum_score')
            ->sum('non_existing_key')
            ->sum('non_existing_key_custom', 'non_existing_key_custom')
            ->avg('score', 'avg_score')
            ->distinct('score', 'distinct_score');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertNotEmpty($agg->get()['sum_score']);
        Assert::assertNotEmpty($agg->get()['avg_score']);
        Assert::assertNotEmpty($agg->get()['distinct_score']);
        Assert::assertEquals(10, $agg->get('non_existing_key_not_in_select', 10));
        Assert::assertEquals(10, $agg->get('non_existing_key', 10));
        Assert::assertEquals(10, $agg->get('non_existing_key_custom', 10));
    }

    public function testReturnCustomKeyAndDefaultWithGroupBy(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->groupBy('con_id')
            ->sum('score', 'sum_score')
            ->sum('non_existing_key')
            ->sum('non_existing_key_custom', 'non_existing_key_custom')
            ->avg('score', 'avg_score')
            ->distinct('score', 'distinct_score');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertNotEmpty($agg->get()[1]['sum_score']);
        Assert::assertNotEmpty($agg->get()[1]['avg_score']);
        Assert::assertNotEmpty($agg->get()[1]['distinct_score']);
        Assert::assertNotEmpty($agg->get()[2]['sum_score']);
        Assert::assertNotEmpty($agg->get()[2]['avg_score']);
        Assert::assertNotEmpty($agg->get()[2]['distinct_score']);
        Assert::assertEquals(10, $agg->get('non_existing_key_not_in_select', 10));
        Assert::assertEquals(10, $agg->get('non_existing_key', 10)[1]['non_existing_key']);
        Assert::assertEquals(10, $agg->get('non_existing_key_custom', 10)[1]['non_existing_key_custom']);
        Assert::assertEquals(10, $agg->get('non_existing_key', 10)[2]['non_existing_key']);
        Assert::assertEquals(10, $agg->get('non_existing_key_custom', 10)[2]['non_existing_key_custom']);
    }

    public function testCountIf(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->countIf('score')
            ->countIf(function ($row) {
                return $row['score'] >= .8;
            }, 'good_score');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertEquals(5, $agg->get()['score']);
        Assert::assertEquals(2, $agg->get()['good_score']);
    }

    public function testCountIfGroupBy(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->groupBy('con_id')
            ->countIf('score')
            ->countIf(function ($row) {
                return $row['score'] >= .8;
            }, 'good_score');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertEquals(3, $agg->get()[1]['score']);
        Assert::assertEquals(1, $agg->get()[1]['good_score']);
        Assert::assertEquals(2, $agg->get()[2]['score']);
        Assert::assertEquals(1, $agg->get()[2]['good_score']);
    }

    public function testDistinct(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->distinct('lesson_id');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertCount(2, $agg->get()['lesson_id']);
        Assert::assertContains('a', $agg->get()['lesson_id']);
        Assert::assertContains('b', $agg->get()['lesson_id']);
    }

    public function testDistinctGroupBy(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->groupBy('con_id')
            ->distinct('lesson_id');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertCount(2, $agg->get()[1]['lesson_id']);
        Assert::assertContains('a', $agg->get()[1]['lesson_id']);
        Assert::assertContains('b', $agg->get()[1]['lesson_id']);
        Assert::assertCount(1, $agg->get()[2]['lesson_id']);
        Assert::assertContains('a', $agg->get()[2]['lesson_id']);
    }

    public function testAvg(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->avg('points', 'avg_points')
            ->avg('score', 'avg_score');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertEquals(160, $agg->get()['avg_points']);
        Assert::assertEquals(0.56, $agg->get()['avg_score']);
    }

    public function testAvgGroupBy(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->groupBy('con_id')
            ->avg('points', 'avg_points')
            ->avg('score', 'avg_score');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertEquals(200, $agg->get()[1]['avg_points']);
        Assert::assertEquals(0.5, $agg->get()[1]['avg_score']);
        Assert::assertEquals(100, $agg->get()[2]['avg_points']);
        Assert::assertEquals(0.65, $agg->get()[2]['avg_score']);
    }

    public function testSum(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->sum('points', 'sum_points')
            ->sum('score', 'sum_score');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertEquals(800, $agg->get()['sum_points']);
        Assert::assertEquals(2.8, $agg->get()['sum_score']);
    }

    public function testSumGroupBy(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->groupBy('con_id')
            ->sum('points', 'sum_points')
            ->sum('score', 'sum_score');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertEquals(600, $agg->get()[1]['sum_points']);
        Assert::assertEquals(1.5, $agg->get()[1]['sum_score']);
        Assert::assertEquals(200, $agg->get()[2]['sum_points']);
        Assert::assertEquals(1.3, $agg->get()[2]['sum_score']);
    }

    public function testCallbackIsCalledEveryNRecordsAdded(): void
    {
        // Arrange
        $called_1 = 0;
        $called_2 = 0;
        $agg = AggregatorService::create();

        // Act
        $agg->every(1, function () use (&$called_1) {
            $called_1++;
        })->every(2, function () use (&$called_2) {
            $called_2++;
        });
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertEquals(5, $called_1);
        Assert::assertEquals(2, $called_2);
    }

    public function testMin(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->min('score', 'min_score');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertEquals(0.2, $agg->get()['min_score']);
    }

    public function testMinGroupBy(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->min('score', 'min_score')
        ->groupBy('con_id');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertEquals(0.2, $agg->get()[1]['min_score']);
        Assert::assertEquals(0.5, $agg->get()[2]['min_score']);
    }

    public function testMax(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->max('score', 'min_score');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertEquals(0.8, $agg->get()['min_score']);
    }

    public function testMaxGroupBy(): void
    {
        // Arrange
        $agg = AggregatorService::create();

        // Act
        $agg->max('score', 'min_score')
            ->groupBy('con_id');
        foreach ($this->getData() as $data) {
            $agg->add($data);
        }

        // Assert
        Assert::assertEquals(0.8, $agg->get()[1]['min_score']);
        Assert::assertEquals(0.8, $agg->get()[2]['min_score']);
    }

    protected function getData(): array
    {
        return [
            [
                'con_id' => 1,
                'points' => 100,
                'score' => 0.8,
                'lesson_id' => 'a',
            ],
            [
                'con_id' => 1,
                'points' => 100,
                'score' => 0.5,
                'lesson_id' => 'a',
            ],
            [
                'con_id' => 1,
                'points' => 400,
                'score' => 0.2,
                'lesson_id' => 'b',
            ],
            [
                'con_id' => 2,
                'points' => 100,
                'score' => 0.5,
                'lesson_id' => 'a',
            ],
            [
                'con_id' => 2,
                'points' => 100,
                'score' => 0.8,
            ],
        ];
    }
}