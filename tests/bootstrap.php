<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// crée le kernel
$kernel = new \App\Kernel('test', true);
$kernel->boot();

// crée l'application
$application = new Application($kernel);
$application->setAutoExit(false);

// recrée la base de données de test
$input = new ArrayInput([
    'command' => 'doctrine:database:drop',
    '--force' => true,
    '--env' => 'test',
]);
$application->run($input, new ConsoleOutput());

$input = new ArrayInput([
    'command' => 'doctrine:database:create',
    '--env' => 'test',
]);
$application->run($input, new ConsoleOutput());

$input = new ArrayInput([
    'command' => 'doctrine:schema:create',
    '--env' => 'test',
]);
$application->run($input, new ConsoleOutput());

// charge les fixtures
$input = new ArrayInput([
    'command' => 'doctrine:fixtures:load',
    '--no-interaction' => true,
    '--env' => 'test',
]);
$application->run($input, new ConsoleOutput());
