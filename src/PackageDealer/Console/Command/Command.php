<?php

namespace PackageDealer\Console\Command;

use PackageDealer\Config\Extra;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\IO\ConsoleIO;
use Composer\DependencyResolver\Pool;
use Composer\Factory;
use Composer\Json\JsonFile;
use PackageDealer\Console\Helper\ConsoleIO as IOHelper;
use PackageDealer\Console\Helper\Provider as ProviderHelper;

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

    /**
     * @var array
     */
    protected $packages = null;

    /**
     * @var \Composer\DependencyResolver\Pool
     */
    protected $pool = null;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
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

    /**
     * @return array
     */
    protected function getPackages()
    {
        if ($this->packages === null) {
            $this->packages = array();
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