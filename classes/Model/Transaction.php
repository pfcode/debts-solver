<?php

namespace App\Model;

use RuntimeException;
use Transliterator;

class Transaction
{
    public string $payer;
    public string $beneficiary;
    public float $amount;

    public function __construct(string $payer, string $beneficiary, float $amount)
    {
        $this->payer = self::normalizeName($payer);
        $this->beneficiary = self::normalizeName($beneficiary);
        $this->amount = $amount;
    }

    public function isAbstract(): bool
    {
        return $this->isPayerAbstract() || $this->isBeneficiaryAbstract();
    }

    public function isPayerAbstract(): bool
    {
        return self::isNameAbstract($this->payer);
    }

    public function isBeneficiaryAbstract(): bool
    {
        return self::isNameAbstract($this->beneficiary);
    }

    private static function isNameAbstract(string $name): bool
    {
        return $name[0] === '[' && $name[strlen($name) - 1] === ']';
    }

    public function isRedundant(): bool
    {
        return $this->payer === $this->beneficiary || $this->amount === 0.0;
    }

    private static function normalizeName(string $name): string
    {
        static $transliterator = null;

        if ($transliterator === null) {
            $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;',
                Transliterator::FORWARD);

            if ($transliterator === null) {
                throw new RuntimeException("Failed to create a Transliterator object");
            }
        }

        return $transliterator->transliterate(strtolower(trim($name)));
    }

    public function reverse(): void
    {
        $beneficiary = $this->beneficiary;
        $this->beneficiary = $this->payer;
        $this->payer = $beneficiary;
        $this->amount = -$this->amount;
    }
}