<?php

namespace PackageDealer\Console\Command;

use Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument;

abstract class Config extends Command
{
    abstract protected function get();
    
    abstract protected function set($value);
    
    public function configure()
    {
        parent::configure();
        $this->addArgument(
            'value',
            InputArgument::OPTIONAL,
            '[optional] The new value for the configuration option'
        );
    }
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->ensureConfigExists();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $value = $input->getArgument('value');
        if ($value) {
            $this->set($value);
            $this->config->write();
            $this->io->info(sprintf(
                '"%s" updated! [%s]',
                $this->getName(),
                $this->io->format(
                    $value, 'comment'
                )
            ));
        } else {
            $this->io->info(sprintf(
                '%s: "%s"',
                $this->getName(),
                $this->get()
            ));
        }
    }
}