<?php

namespace PackageDealer\Console\Command;

use Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

abstract class Provider extends Command
{
    protected function configure()
    {
        $this->addArgument('provider', \Symfony\Component\Console\Input\InputArgument::REQUIRED);
        $this->addOption('type', 't', \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'The provider type.', 'vcs');
        return parent::configure();
    }
}