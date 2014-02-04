<?php

namespace PackageDealer\Console\Command;

use PackageDealer\Config\Config,
    Symfony\Component\Console\Command\Command as BaseCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends BaseCommand
{
    /**
     * @var \PackageDealer\Console\Helper\ConsoleIO
     */
    protected $io = null;
    /**
     * @var \PackageDealer\Config\Config 
     */
    protected $config = null;
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = $this->getHelper('consoleio');
        $this->config = new Config($input->getOption('config'));
    }
    
    protected function ensureConfigExists()
    {
        if (!$this->config->exists()) {
            throw new \RuntimeException('Configuration file does not exist.');
        }
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        var_dump(get_class($this));
    }
    
    protected function getComposer()
    {
        return \Composer\Factory::create($this->io->getIO(), $this->config->toArray());
    }
}