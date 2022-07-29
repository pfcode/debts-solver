<?php

namespace App\Reader;

use App\Model\Transaction;
use App\Model\TransactionSet;
use ErrorException;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class XlsxReader
{
    private string $path;
    private string $columnPayer;
    private string $columnBeneficiary;
    private string $columnAmount;
    private int $skipRows;

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @param string $columnPayer
     */
    public function setColumnPayer(string $columnPayer): void
    {
        $this->columnPayer = $columnPayer;
    }

    /**
     * @param string $columnBeneficiary
     */
    public function setColumnBeneficiary(string $columnBeneficiary): void
    {
        $this->columnBeneficiary = $columnBeneficiary;
    }

    /**
     * @param string $columnAmount
     */
    public function setColumnAmount(string $columnAmount): void
    {
        $this->columnAmount = $columnAmount;
    }

    /**
     * @param int $skipRows
     */
    public function setSkipRows(int $skipRows): void
    {
        $this->skipRows = $skipRows;
    }

    /**
     * @return \App\Model\TransactionSet
     * @throws \ErrorException
     * @throws \PhpOffice\PhpSpreadsheet\Calculation\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function readTransactionSet(): TransactionSet
    {
        $reader = new Xlsx();
        if (!$reader->canRead($this->path)) {
            throw new ErrorException("File $this->path couldn't be open.");
        }

        $spreadsheet = $reader->load($this->path);
        $worksheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
        $set = new TransactionSet();

        foreach ($worksheet->getRowIterator(1 + $this->skipRows) as $row) {
            $rowIdx = $row->getRowIndex();

            $payer = $worksheet->getCell("$this->columnPayer$rowIdx")->getFormattedValue();
            $beneficiary = $worksheet->getCell("$this->columnBeneficiary$rowIdx")->getFormattedValue();
            $amount = (float)$worksheet->getCell("$this->columnAmount$rowIdx")->getCalculatedValue();

            if (empty($payer) || empty($beneficiary) || empty($amount)) {
                continue;
            }

            $set->add(new Transaction($payer, $beneficiary, $amount));
        }

        $set->removeZeroAmountTransactions();
        $set->removeRedundantTransactions();

        return $set;
    }
}