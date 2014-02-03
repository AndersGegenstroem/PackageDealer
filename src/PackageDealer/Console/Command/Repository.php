<?php

namespace PackageDealer\Console\Command;

use Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument;

abstract class Repository extends Command
{
    protected function configure()
    {
        $this->addArgument(
            'url', InputArgument::REQUIRED, 'The repository url'
        );
    }
    
    /**
     * @param InputInterface  $input  The input instance
     * @param OutputInterface $output The output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $input->getOption('config');
        var_dump($config);
        $output->writeln(__METHOD__);
    }
}