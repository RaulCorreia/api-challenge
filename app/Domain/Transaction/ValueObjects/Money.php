<?php

namespace App\Domain\Transaction\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(
        public readonly float $amount
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }
    }

    public function isGreaterThan(Money $other): bool
    {
        return $this->amount > $other->amount;
    }

    public function isLessThan(Money $other): bool
    {
        return $this->amount < $other->amount;
    }

    public function add(Money $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function subtract(Money $other): self
    {
        return new self($this->amount - $other->amount);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount;
    }

    public function format(): string
    {
        return number_format($this->amount, 2, ',', '.');
    }

    public function __toString(): string
    {
        return (string) $this->amount;
    }
}

