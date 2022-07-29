<?php

namespace App\Operation;

use App\Model\Transaction;
use App\Model\TransactionSet;
use RuntimeException;

class AbstractResolver
{
    /**
     * Removes all abstract transactions from the set.
     * Will fail if any abstract payer transactions group does not sum to zero.
     *
     * @param \App\Model\TransactionSet $transactionSet
     * @return \App\Model\TransactionSet
     */
    public function resolve(TransactionSet $transactionSet): TransactionSet
    {
        $set = $transactionSet->duplicate();
        $this->throwIfAnyTransactionGroupDoesNotSumToZero($set);

        do {
            $hasChanged = false;

            // Iterate through transactions with abstract payers.
            foreach ($set->toArray() as $byAbstract) {
                if (!$byAbstract->isPayerAbstract()) {
                    continue;
                }

                // Find any transaction for current abstract with real beneficiary
                foreach ($set->toArray() as $byReal) {
                    if (!($byReal !== $byAbstract && $byReal->beneficiary === $byAbstract->payer)) {
                        continue;
                    }

                    $amount = min($byReal->amount, $byAbstract->amount);
                    $byReal->amount -= $amount;
                    $byAbstract->amount -= $amount;

                    if($byReal->payer !== $byAbstract->beneficiary) {
                        $set->add(new Transaction($byReal->payer, $byAbstract->beneficiary, $amount));
                    }

                    $hasChanged = true;
                    break 2;
                }
            }

            $set->removeZeroAmountTransactions();
        } while ($hasChanged);

        $this->throwIfHasAnyAbstractTransactions($set);

        return $set;
    }

    private function throwIfHasAnyAbstractTransactions(TransactionSet $set): void
    {
        foreach ($set->toArray() as $transaction) {
            if ($transaction->isAbstract()) {
                throw new RuntimeException("Given TransactionSet was expected to have no abstract transactions.");
            }
        }
    }

    private function throwIfAnyTransactionGroupDoesNotSumToZero(TransactionSet $set): void
    {
        $sum = 0;

        foreach ($set->toArray() as $transaction) {
            if ($transaction->isPayerAbstract()) {
                $sum += $transaction->amount;
            } elseif ($transaction->isBeneficiaryAbstract()) {
                $sum -= $transaction->amount;
            }
        }

        if (round($sum, 2) !== 0.0) {
            throw new RuntimeException("Some abstract transactions does not sum to zero. ".
                "Please check if input data is correct.");
        }
    }
}