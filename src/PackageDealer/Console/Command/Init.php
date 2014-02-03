<?php

namespace PackageDealer\Console\Command;

use Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputOption;

class Init extends Command
{
    protected function configure()
    {
        $this->setName('init')
             ->setDescription('Creates a new configuration file.')
             ->setDefinition(array(
                 new InputOption(
                     'force',
                     'f',
                     InputOption::VALUE_NONE,
                     'Forces overwriting of existing config file'
                 )
             ));
    }
    /**
     * @param InputInterface  $input  The input instance
     * @param OutputInterface $output The output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->info(sprintf(
            'Creating PackageDealer configuration file at [%s]',
            $this->config->getPath()
        ));
        
        if (!$this->config->isWriteable()) {
            return $this->io->error('Config file is not writeable.');
        }
        
        if (!$input->getOption('force') && $this->config->exists()) {
            $this->io->comment('Config file already exists!');
            if ($this->io->ask('Do you want to overwrite it? ', array('yes', 'no')) !== 'yes') {
                return null;
            }
        }
        
        $this->io->info(
            'Please answer the following questions to create your configuration.'
        );
        $this->configureHomepage();
        
        if ($this->io->ask('Do you want PackageDealer to store local copies for repositories?', array('yes','no')) === 'yes') {
            $this->configureArchive();
        }
        
        $this->config->write();
        
        $this->io->comment(sprintf(
            'Config file written to %s',
            $this->config->getPath()
        ));
    }
    
    private function configureHomepage()
    {
        $this->config->homepage->title = $this->io->askRequired(
            'Enter a name for your website: '
        );
        $this->config->homepage->description = $this->io->ask(
            'Enter a short description for your website: '
        );
        $this->config->homepage->docroot = $this->io->askRequired(
            'Enter the docroot your website: ', array(), 5, function($docroot) {
                $return = true;
                if (is_dir($docroot)) {
                    if (!is_writable($docroot)) {
                        $return = sprintf(
                            ' DocumentRoot [%s] not writeable! ',
                            $docroot
                        );
                    }
                    try {
                        $it = new \DirectoryIterator($docroot);
                        foreach ($it as $child) {
                            if (!$it->isDot()) {
                                $return = sprintf(
                                    ' DocumentRoot [%s] not empty! ',
                                    $docroot
                                );
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        $return = sprintf(
                            ' DocumentRoot [%s] not readable! ',
                            $docroot
                        );
                    }
                } else {
                    $parentDir = dirname($docroot);
                    if (!is_dir($parentDir)) {
                        $return = sprintf(
                            ' Parent directory [%s] does not exist! ',
                            $parentDir
                        );
                    } elseif (!is_writeable($parentDir)) {
                        $return = sprintf(
                            ' Parent directory [%s] is not writeable! ',
                            $parentDir
                        );
                    }
                }
                return $return;
            }
        );
    }
    
    private function configureArchive()
    {
        $this->config->archive->path = $this->io->askRequired(sprintf(
            'Enter the path extension for your archive files within your docroot: %s',
            $this->io->format($this->config->homepage->docroot . '/', 'comment')
        ));
        $this->config->archive->type = $this->io->askRequired(
            sprintf(
                'Enter the datatype, which shall be used to store files',
                $this->io->format($this->config->homepage->docroot . '/', 'comment')
            ),
            array('zip','tar'),
            5,
            function ($value) {
                return $value === 'zip' || $value === 'tar'
                    ? true
                    : ' Value must be "zip" or "tar"';
            });
    }
}