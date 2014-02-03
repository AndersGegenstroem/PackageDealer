<?php

namespace PackageDealer\Console\Command\Repository;

use PackageDealer\Console\Command\Repository,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

class Remove extends Repository
{
    protected function configure()
    {
        parent::configure();
        $this->setName('repository/remove')
             ->setDescription('Removes a repository to the composer repository list');
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