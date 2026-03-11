<?php

namespace Tests\Unit\Domain;

use App\Domain\Transaction\ValueObjects\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_can_be_created_with_positive_amount(): void
    {
        $money = new Money(100.00);

        $this->assertSame(100.00, $money->amount);
    }

    public function test_can_be_created_with_zero(): void
    {
        $money = new Money(0.0);

        $this->assertSame(0.0, $money->amount);
    }

    public function test_throws_when_amount_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Money amount cannot be negative.');

        new Money(-0.01);
    }

    public function test_is_greater_than(): void
    {
        $a = new Money(50.0);
        $b = new Money(30.0);

        $this->assertTrue($a->isGreaterThan($b));
        $this->assertFalse($b->isGreaterThan($a));
    }

    public function test_is_less_than(): void
    {
        $a = new Money(10.0);
        $b = new Money(20.0);

        $this->assertTrue($a->isLessThan($b));
        $this->assertFalse($b->isLessThan($a));
    }

    public function test_add_two_amounts(): void
    {
        $a      = new Money(40.00);
        $b      = new Money(60.00);
        $result = $a->add($b);

        $this->assertSame(100.00, $result->amount);
    }

    public function test_subtract_amounts(): void
    {
        $a      = new Money(100.00);
        $b      = new Money(35.50);
        $result = $a->subtract($b);

        $this->assertSame(64.50, $result->amount);
    }

    public function test_equality(): void
    {
        $a = new Money(99.99);
        $b = new Money(99.99);
        $c = new Money(1.00);

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function test_formats_as_brazilian_currency(): void
    {
        $money = new Money(1234567.89);

        $this->assertSame('1.234.567,89', $money->format());
    }

    public function test_casts_to_string(): void
    {
        $money = new Money(42.5);

        $this->assertSame('42.5', (string) $money);
    }
}

