<?php

namespace App\Operation;

use App\Model\Transaction;
use App\Model\TransactionSet;

class Centralizer
{
    /**
     * Makes all transaction to be sent or received by a given person.
     * @param \App\Model\TransactionSet $transactionSet
     * @param string $centralPerson
     * @return \App\Model\TransactionSet
     */
    public function centralize(TransactionSet $transactionSet, string $centralPerson): TransactionSet
    {
        $set = $transactionSet->duplicate();

        // Find transactions that have not been already paid by the central person.
        foreach ($set->toArray() as $transaction) {
            if ($transaction->payer === $centralPerson) {
                continue;
            }

            if ($transaction->beneficiary === $centralPerson) {
                $transaction->reverse();
            } else {
                // Split transaction in two

                $set->add(new Transaction($centralPerson, $transaction->payer, -$transaction->amount));
                $set->add(new Transaction($centralPerson, $transaction->beneficiary, $transaction->amount));
                $set->remove($transaction);
            }
        }

        return $set;
    }
}