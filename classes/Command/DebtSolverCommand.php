<?php

namespace App\Command;

use App\Model\Transaction;
use App\Model\TransactionSet;
use App\Operation\AbstractResolver;
use App\Operation\Centralizer;
use App\Operation\Consolidator;
use App\Operation\Inverser;
use App\Operation\Minimizer;
use App\Reader\XlsxReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DebtSolverCommand extends Command
{
    protected static $defaultName = "app:calculate";

    protected function configure(): void
    {
        $this->setDescription("Calculates an optimized set of transactions based on a given spreadsheet ".
            "that contains a list of transactions between people. Allows to minimize amount of transfers required to ".
            "pay off mutual debts.");

        $this->addArgument("path", InputArgument::REQUIRED, "Path to the spreadsheet file.");
        $this->addArgument("col-payer", InputArgument::REQUIRED, "Letter of a column with payer name.");
        $this->addArgument("col-beneficiary", InputArgument::REQUIRED,
            "Letter of a column with beneficiary name.");
        $this->addArgument("col-amount", InputArgument::REQUIRED, "Letter of a column with transaction amount.");

        $this->addOption("skip-rows", "s", InputOption::VALUE_OPTIONAL, "Amount of rows to skip.", 0);
        $this->addOption("proxy-person", "pp", InputOption::VALUE_OPTIONAL,
            "Name of a person that will handle all final transactions.");
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \ErrorException
     * @throws \PhpOffice\PhpSpreadsheet\Calculation\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reader = new XlsxReader();
        $reader->setColumnPayer($input->getArgument('col-payer'));
        $reader->setColumnBeneficiary($input->getArgument('col-beneficiary'));
        $reader->setColumnAmount($input->getArgument('col-amount'));
        $reader->setPath($input->getArgument('path'));
        $reader->setSkipRows($input->getOption('skip-rows'));

        $set = $reader->readTransactionSet();

        $abstractResolver = new AbstractResolver();
        $consolidator = new Consolidator();
        $inverser = new Inverser();
        $minimizer = new Minimizer();

        $set = $abstractResolver->resolve($set);

        if (($proxyPerson = $input->getOption('proxy-person')) !== null) {
            $centralizer = new Centralizer();
            $set = $centralizer->centralize($set, $proxyPerson);
        }

        $set = $consolidator->consolidate($set);
        $set = $minimizer->minimize($set);
        $set = $inverser->inverse($set);

        $this->dumpTransactionSet($set, $output);

        return Command::SUCCESS;
    }

    private function dumpTransactionSet(TransactionSet $set, OutputInterface $output, bool $pretty = true): void
    {
        $table = [];

        if ($pretty) {
            $table[] = ["Payer", "Beneficiary", "Amount"];
            $table[] = ["---", "---", "---"];
        }

        $transactions = $set->toArray();

        usort($transactions, static function (Transaction $a, Transaction $b) {
            if ($a->payer === $b->payer) {
                return $a->amount < $b->amount;
            }

            return $a->payer > $b->payer;
        });

        foreach ($transactions as $transaction) {
            $amount = $transaction->amount;

            if ($pretty) {
                $amount = number_format($amount, 2);
            }

            $table[] = [$transaction->payer, $transaction->beneficiary, $amount];
        }

        foreach ($table as $row) {
            $output->write([
                str_pad($row[0], 16),
                " ",
                str_pad($row[1], 16),
                " ",
                $row[2],
                "\n",
            ]);
        }
    }
}