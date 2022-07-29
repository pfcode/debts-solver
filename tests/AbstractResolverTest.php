<?php

use App\Model\Transaction;
use App\Model\TransactionSet;
use App\Operation\AbstractResolver;
use PHPUnit\Framework\TestCase;

final class AbstractResolverTest extends TestCase
{
    public function testOne(): void
    {
        $input = new TransactionSet([
            new Transaction("a", "[z]", 100.00),
            new Transaction("[z]", "b", 80.00),
            new Transaction("[z]", "c", 20.00),
        ]);

        $output = (new AbstractResolver())->resolve($input);

        $this->assertCount(2, $output->toArray());
        $this->assertEquals(80.00, $output->findFirstByPayerAndBeneficiary("a", "b")?->amount);
        $this->assertEquals(20.00, $output->findFirstByPayerAndBeneficiary("a", "c")?->amount);
    }

    public function testTwo(): void
    {
        $input = new TransactionSet([
            new Transaction("a", "[z]", 100.00),
            new Transaction("[z]", "a", 80.00),
            new Transaction("[z]", "c", 20.00),
        ]);

        $output = (new AbstractResolver())->resolve($input);

        $this->assertCount(1, $output->toArray());
        $this->assertEquals(20.00, $output->findFirstByPayerAndBeneficiary("a", "c")?->amount);
    }

    public function testThree(): void
    {
        $input = new TransactionSet([
            new Transaction("a", "[z]", 100.00),
            new Transaction("b", "[z]", 20.00),
            new Transaction("[z]", "a", 2.00),
            new Transaction("[z]", "b", 8.00),
            new Transaction("[z]", "c", 110.00),
            new Transaction("i", "[y]", 100.00),
            new Transaction("[y]", "i", 80.00),
            new Transaction("[y]", "j", 20.00),
        ]);

        $output = (new AbstractResolver())->resolve($input);

        $this->assertCount(4, $output->toArray());
        $this->assertEquals(8.00, $output->findFirstByPayerAndBeneficiary("a", "b")?->amount);
        $this->assertEquals(90.00, $output->findFirstByPayerAndBeneficiary("a", "c")?->amount);
        $this->assertEquals(20.00, $output->findFirstByPayerAndBeneficiary("b", "c")?->amount);
        $this->assertEquals(20.00, $output->findFirstByPayerAndBeneficiary("i", "j")?->amount);
    }
    public function testFour(): void
    {
        $input = new TransactionSet([
            new Transaction("a", "[z]", 100.00),
            new Transaction("[z]", "b", 85.00),
            new Transaction("[z]", "c", 20.00),
        ]);

        $this->expectException(Exception::class);
        (new AbstractResolver())->resolve($input);
    }
}