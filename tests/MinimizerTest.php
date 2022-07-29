<?php

use App\Model\Transaction;
use App\Model\TransactionSet;
use App\Operation\Minimizer;
use PHPUnit\Framework\TestCase;

class MinimizerTest extends TestCase
{
    public function testOne(): void
    {
        $input = new TransactionSet([
            new Transaction("a", "b", 10.00),
            new Transaction("b", "a", 10.00),
            new Transaction("x", "y", 20.00),
            new Transaction("y", "x", 10.00),
            new Transaction("c", "d", 10.00),
            new Transaction("d", "c", 20.00),
        ]);

        $output = (new Minimizer())->minimize($input);

        $this->assertEquals(0.00, $output->findFirstByPayerAndBeneficiary("a", "b")?->amount);
        $this->assertEquals(10.00, $output->findFirstByPayerAndBeneficiary("x", "y")?->amount);
        $this->assertEquals(10.00, $output->findFirstByPayerAndBeneficiary("d", "c")?->amount);
    }
}