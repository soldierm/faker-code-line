<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Who\FakerCodeLine\Command\AutoCommitCommand;

$application = new Application();
$application->add(new AutoCommitCommand(require __DIR__ . '/config.php'));
$application->run();