<?php

namespace App\Operation;

use App\Model\TransactionSet;

class Inverser
{
    /**
     * Swaps beneficiary and payer of all transactions with negative amount.
     * All transactions in the output set will be larger or equal to 0.
     * @param \App\Model\TransactionSet $input
     * @return \App\Model\TransactionSet
     */
    public function inverse(TransactionSet $input): TransactionSet
    {
        $set = $input->duplicate();

        foreach ($set->toArray() as $transaction) {
            if ($transaction->amount < 0) {
                $transaction->reverse();
            }
        }

        return $set;
    }
}