<?php

use App\Command\DebtSolverCommand;
use Symfony\Component\Console\Application;

require_once __DIR__.'/../vendor/autoload.php';

$command = new DebtSolverCommand();

$app = new Application("debts-solver");
$app->add($command);
$app->setDefaultCommand($command->getName(), true);
$app->run();