<?php

namespace PackageDealer\Console\Command;

use Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

class Build extends Command
{
    protected function configure()
    {
        $this->setName('build')
             ->setDescription('Scans repositories, writes archive files and creates webpage');
    }
    
    /**
     * @param InputInterface  $input  The input instance
     * @param OutputInterface $output The output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(__METHOD__);
    }
}
