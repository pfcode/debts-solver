<?php

namespace App\Operation;

use App\Model\TransactionSet;

class Minimizer
{
    /**
     * Merges transactions with opposing payer/beneficiary relation.
     * This implementation won't make new transactions with negative amounts.
     * @param \App\Model\TransactionSet $input
     * @return \App\Model\TransactionSet
     */
    public function minimize(TransactionSet $input): TransactionSet
    {
        $set = $input->duplicate();

        do {
            $hasChanged = false;

            foreach ($set->toArray() as $transaction) {
                foreach ($set->toArray() as $reverse) {
                    if ($transaction !== $reverse
                        && $reverse->beneficiary === $transaction->payer
                        && $reverse->payer === $transaction->beneficiary) {

                        if ($reverse->amount > $transaction->amount) {
                            $reverse->amount -= $transaction->amount;
                            $set->remove($transaction);
                        } else {
                            $transaction->amount -= $reverse->amount;
                            $set->remove($reverse);
                        }

                        $hasChanged = true;
                        break 2;
                    }
                }
            }
        } while ($hasChanged);

        return $set;
    }
}