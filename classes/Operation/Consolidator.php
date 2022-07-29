<?php

namespace App\Operation;

use App\Model\TransactionSet;

class Consolidator
{
    /**
     * This function merges all transactions having same payer, beneficiary and payment direction.
     * @param \App\Model\TransactionSet $inputSet
     * @return \App\Model\TransactionSet
     */
    public function consolidate(TransactionSet $inputSet): TransactionSet
    {
        $outputSet = new TransactionSet();

        foreach ($inputSet->toArray() as $transaction) {
            if ($entry = $outputSet->findFirstByPayerAndBeneficiary($transaction->payer, $transaction->beneficiary)) {
                $entry->amount += $transaction->amount;
            } else {
                $outputSet->add(clone $transaction);
            }
        }

        return $outputSet;
    }
}