<?php

namespace PackageDealer\Console\Command\Repository;

use PackageDealer\Console\Command\Repository,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument;

class Add extends Repository
{
    protected function configure()
    {
        parent::configure();
        $this->setName('repository/add')
             ->setDescription('Adds a repository to the composer repository list')
             ->addArgument(
                 'type', InputArgument::OPTIONAL, 'The repository type', 'vcs'
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