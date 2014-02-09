<?php

namespace PackageDealer\Console\Command;

use PackageDealer\Config\Extra,
    Symfony\Component\Console\Command\Command as BaseCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Composer\IO\ConsoleIO,
    Composer\DependencyResolver\Pool,
    Composer\Factory,
    Composer\Json\JsonFile,
    PackageDealer\Console\Helper\ConsoleIO as IOHelper,
    PackageDealer\Console\Helper\Provider as ProviderHelper;

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
    /**
     * @var \Composer\Composer
     */
    protected $composer = null;
    
    protected $packages = null;
    
    protected $pool = null;
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $helper   = $this->getHelperSet();
        $io       = new ConsoleIO($input, $output, $this->getHelperSet());
        $this->io = new IOHelper($io, $this->getHelperSet());
        $configFile = $input->getOption('config');
        
        if (!$this->getApplication()->loadConfig($configFile)) {
            $this->io->error('Cannot find config file at: [' . $configFile . ']');
            exit(1);
        }
        
        $helper->set($this->io);
        $helper->set(new ProviderHelper());
        $this->composer = Factory::create($io, $input->getOption('config'));
    }
    /**
     * @return \PackageDealer\Config\Extra
     */
    protected function getExtraConfig()
    {
        return new Extra($this->composer->getPackage());
    }
    /**
     * @return \Composer\DependencyResolver\Pool
     */
    protected function getProviders()
    {
        if ($this->pool === null) {
            $this->pool = new Pool(
                $this->composer->getPackage()->getMinimumStability()
            );
            foreach ($this->composer->getRepositoryManager()->getRepositories() as $repository) {
                $this->pool->addRepository($repository);
            }
        }
        return $this->pool;
    }
    
    protected function getPackages()
    {
        if ($this->packages === null) {
            $loader = new \Composer\Package\Loader\ArrayLoader();
            $packageFile = new JsonFile(
                $this->getExtraConfig()->getDocroot() .
                DIRECTORY_SEPARATOR .
                'packages.json'
            );
            if ($packageFile->exists()) {
                $packages = $packageFile->read();
                foreach (array_shift($packages) as $stack) {
                    foreach ($stack as $package) {
                        $this->packages[] = $loader->load($package);
                    }
                }
            }
        }
        return $this->packages;
    }
}