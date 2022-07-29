<?php

namespace App\Model;

class TransactionSet
{
    /** @var \App\Model\Transaction[] */
    public array $contents;

    public function __construct(array $transactions = [])
    {
        $this->contents = $transactions;
    }

    public function isEmpty(): bool
    {
        return empty($this->contents);
    }

    public function toArray(): array
    {
        return $this->contents;
    }

    public function add(Transaction $transaction): void
    {
        $this->contents[] = $transaction;
    }

    public function remove(Transaction $transaction): void
    {
        foreach ($this->contents as $k => $iteratedTransaction) {
            if ($transaction === $iteratedTransaction) {
                unset($this->contents[$k]);
            }
        }
    }

    public function findFirstByPayerAndBeneficiary(string $payer, string $beneficiary): ?Transaction
    {
        foreach ($this->contents as $transaction) {
            if ($transaction->beneficiary === $beneficiary && $transaction->payer === $payer) {
                return $transaction;
            }
        }

        return null;
    }

    public function removeZeroAmountTransactions(): void
    {
        foreach ($this->contents as $transaction) {
            if (round($transaction->amount, 2) === 0.00) {
                $this->remove($transaction);
            }
        }
    }

    public function removeRedundantTransactions(): void
    {
        foreach ($this->contents as $transaction) {
            if ($transaction->isRedundant()) {
                $this->remove($transaction);
            }
        }
    }

    public function duplicate(): TransactionSet
    {
        $set = new TransactionSet();

        foreach ($this->contents as $transaction) {
            $set->add(clone $transaction);
        }

        return $set;
    }
}