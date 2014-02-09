<?php

namespace PackageDealer\Console\Command\Provider;

use Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputOption,
    PackageDealer\Console\Command\Provider,
    Composer\Repository\ArrayRepository,
    ReflectionClass;

class Show extends Provider
{
    protected $types = array('all','vcs','composer');
    
    protected function configure()
    {
        $this->setName('provider/list')
             ->setDescription('Shows all registered providers.')
             ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'The type of providers to show', 'all');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $requiredType = strtolower($input->getOption('type'));
        if (!in_array($requiredType, $this->types)) {
            $this->io->error(sprintf(
                'Invalid "type" [%s]. Please use (%s).',
                $requiredType,
                implode('|', $this->types)
            ));
            $requiredType = 'all';
        }
        
        $table = $this->getHelper('table');
        $table->setHeaders(array('Id', 'Url', 'Type'));
        foreach ($this->composer->getRepositoryManager()->getRepositories() as $repository) {
            $repoType = $this->getRepositoryType($repository);
            if ($requiredType === 'all' || $requiredType === $repoType) {
                $repoUrl = $this->getRepositoryUrl($repository);
                $table->addRow(array(
                    substr(md5($repoUrl), 0, 8),
                    $repoUrl,
                    $repoType,
                ));
            }
        }
        
        if ($requiredType === 'all') {
            $this->io->info('All currently installed providers:');
        } else {
            $this->io->info(sprintf(
                'Currently installed "%s"-providers:',
                $requiredType
            ));
        }
        
        $table->render($output);
    }
    
    protected function getRepositoryUrl(ArrayRepository $repository)
    {
        $class = new ReflectionClass($repository);
        $property = $class->getProperty('url');
        $property->setAccessible(true);
        return $property->getValue($repository);
    }
    
    protected function getRepositoryType(ArrayRepository $repository)
    {
        return strtolower(
            preg_replace(
                '/^(.*)\\\\(.*)Repository$/',
                '\2',
                get_class($repository)
            )
        );
    }
}