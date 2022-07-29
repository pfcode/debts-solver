<?php

use App\Model\Transaction;
use App\Model\TransactionSet;
use App\Operation\Centralizer;
use App\Operation\Consolidator;
use PHPUnit\Framework\TestCase;

final class CentralizerTest extends TestCase
{
    public function testOne(): void
    {
        $input = new TransactionSet([
            new Transaction("a", "b", 100.00),
            new Transaction("b", "a", 50.00),
            new Transaction("b", "c", 15.00),
            new Transaction("c", "a", 20.00),
            new Transaction("c", "b", 5.00),
        ]);

        $output = (new Centralizer())->centralize($input, "a");
        $consolidated = (new Consolidator())->consolidate($output);

        $this->assertCount(2, $consolidated->toArray());
        $this->assertEquals(40.00, $consolidated->findFirstByPayerAndBeneficiary("a", "b")?->amount);
        $this->assertEquals(-10.00, $consolidated->findFirstByPayerAndBeneficiary("a", "c")?->amount);
    }
}