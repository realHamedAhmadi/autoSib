<?php

namespace App\Support;
final class RandomNumberPicker
{
    /**
     * @var array<int, int>
     */
    private array $numbers;

    public function __construct(int $max)
    {
        if ($max < 1) {
            throw new \InvalidArgumentException('The maximum number must be greater than zero.');
        }

        $this->numbers = range(1, $max);
        shuffle($this->numbers);
    }

    public function next(): int
    {
        return $this->numbers === []
            ? 0
            : array_pop($this->numbers);
    }

    public function hasNext(): bool
    {
        return $this->numbers !== [];
    }

    public function remainingCount(): int
    {
        return count($this->numbers);
    }
}
