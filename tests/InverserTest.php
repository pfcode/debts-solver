<?php

use App\Model\Transaction;
use App\Model\TransactionSet;
use App\Operation\Inverser;
use PHPUnit\Framework\TestCase;

class InverserTest extends TestCase
{
    public function testOne(): void
    {
        $input = new TransactionSet([
            new Transaction("a", "b", -10.00),
            new Transaction("x", "y", 10.00),
        ]);

        $output = (new Inverser())->inverse($input);

        $this->assertNull($output->findFirstByPayerAndBeneficiary("a", "b"));
        $this->assertEquals(10.00, $output->findFirstByPayerAndBeneficiary("b", "a")?->amount);
        $this->assertEquals(10.00, $output->findFirstByPayerAndBeneficiary("x", "y")?->amount);
    }
}