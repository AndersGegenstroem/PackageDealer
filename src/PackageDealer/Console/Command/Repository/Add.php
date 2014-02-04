<?php

namespace PackageDealer\Console\Command\Repository;

use PackageDealer\Console\Command\Repository,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Composer\IO\ConsoleIO,
    Composer\Factory,
    Composer\Repository\VcsRepository;

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
        $url = $input->getArgument('url');
        if ($this->findRepository($url)) {
            $this->io->error('Repository already exists!');
        } else {
            try {
                $package = $this->findPackage($url, $input, $output);
                $this->config->repositories->add($package->getName(), array(
                    'type' => $package->getSourceType(),
                    'url'  => $package->getSourceUrl(),
                    'require' => '',
                ));
                $this->io->comment('Connection established!');
                $this->config->write();
                $this->io->info('New configuration file written.');
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());
            }
        }
    }
    /**
     * @param string $url
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return \Composer\Package\CompletePackage
     * @throws \Exception
     */
    protected function findPackage($url, InputInterface $input, OutputInterface $output)
    {
        $this->io->info('Trying to connect to repository.');
        $repo = new VcsRepository(
            array('url' => $url),
            $this->io->getIO(),
            $this->getComposer()->getConfig()
        );
        foreach ($repo->getPackages() as $package) {
            return $package;
        }
    }
}