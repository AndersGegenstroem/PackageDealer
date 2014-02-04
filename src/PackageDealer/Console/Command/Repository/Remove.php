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
        $url  = $input->getArgument('url');
        $name = $this->findRepository($url);
        if ($name) {
            $this->config->repositories->remove($name);
            $this->config->write();
            $this->io->info(sprintf(
                'Repository [%s] successfully removed!',
                $name
            ));
        } else {
            $this->io->error(sprintf(
                'Repository [%s] does not exist!',
                $url
            ));
        }
    }
}