<?php

namespace PackageDealer\Console\Command\Repository;

use PackageDealer\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

class Show extends Command
{
    protected function configure()
    {
        parent::configure();
        $this->setName('repository/list')
             ->setDescription('Shows a list of all packagedealer repositories.');
    }
    
    /**
     * @param InputInterface  $input  The input instance
     * @param OutputInterface $output The output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $table \Symfony\Component\Console\Helper\TableHelper */
        $table = $this->getHelper('table')->setHeaders(array(
            'Name', 'Type', 'URL', 'Require'
        ));
        
        $this->io->info('Currently installed repositories:' . PHP_EOL);
        foreach ($this->config->repositories as $name=>$options) {
            $table->addRow(array(
                $name,
                $options['type'],
                $options['url'],
                $options['require'],
            ));
        }
        $table->render($output);
    }
}