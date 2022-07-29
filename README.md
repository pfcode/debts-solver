# Debts Solver

A simple utility written in PHP (8.1+) to minimize amount of transactions required to pay off debts between people.

### What is the use case?
Assume that you have a Xlsx spreadsheet that contains a list of transactions made by a group of friends, in which
there are (at least) three columns: Payer (string), Beneficiary (string) and Amount (float). On a meet that you were 
all present, a few of you paid bills for the others. If there were multiple payers and a lot of transactions, 
it may not be a trivial task to quickly summarize how much each beneficiary should pay to each payer.
 
### What this utility do?
It does three things:
1. Minimizes amount of transactions required for beneficiaries to pay off their payers.
2. Resolves abstract payers/beneficiaries - useful for whip-rounds.
3. Allows to proxy all transactions by a single person (optional).

### Usage
After installation of the Composer dependencies, this utility can be used as a standalone CLI script:

```shell
php bin/debts-solver.php Transactions.xlsx C D E --skip-rows=1 --proxy-person=xyz
```

Arguments `C`, `D` and `E` in the example above represent columns in the spreadsheet for: Payer, Beneficiary and Amount, 
respectively. For more detailed information about arguments and options, please refer to the help page by calling
`php bin/debts-solver.php --help` from the CLI.

You may also want to use this utility as a library, especially using classes from the `Model` and `Operation` 
directories. Refer to the `Command/DebtSolverCommand.php` file for usage details.

### Technical notice

Rows with invalid data, zero amount or those having the same payer and beneficiary names, are being automatically 
skipped, since they are redundant. All names are normalized using Transliterate class (ext-intl), trimmed and made 
lower-case.

Author of this library does not give any guarantee that output results are correct. Use it only 
for your own responsibility.

### License
This project is published under the MIT license.