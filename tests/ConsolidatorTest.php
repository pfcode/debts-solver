<?php

use App\Model\Transaction;
use App\Model\TransactionSet;
use App\Operation\Consolidator;
use PHPUnit\Framework\TestCase;

final class ConsolidatorTest extends TestCase
{
    public function testOne(): void
    {
        $input = new TransactionSet([
            new Transaction("a", "b", 10.00),
            new Transaction("a", "b", 15.00),
            new Transaction("b", "a", 3.00),
        ]);

        $output = (new Consolidator())->consolidate($input);

        $this->assertCount(2, $output->toArray());
        $this->assertEquals(25.00, $output->findFirstByPayerAndBeneficiary("a", "b")?->amount);
        $this->assertEquals(3.00, $output->findFirstByPayerAndBeneficiary("b", "a")?->amount);
    }

    public function testTwo(): void
    {
        $input = new TransactionSet([
            new Transaction("a", "b", 10.00),
            new Transaction("a", "b", -10.00),
        ]);

        $output = (new Consolidator())->consolidate($input);

        $this->assertCount(1, $output->toArray());
        $this->assertEquals(0.00, $output->findFirstByPayerAndBeneficiary("a", "b")?->amount);
    }
}