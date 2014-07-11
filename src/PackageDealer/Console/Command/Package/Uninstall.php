<?php

namespace PackageDealer\Console\Command\Package;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use PackageDealer\Console\Command\Command;

class Uninstall extends Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('package/uninstall')
             ->setDescription('Uninstalls a package.')
             ->addArgument('package', InputArgument::REQUIRED, 'The package name')
             ->addOption('keep-files', false, InputOption::VALUE_NONE, 'Causes also uninstallation of package downloads')
             ->addOption('skip-build', false, InputOption::VALUE_OPTIONAL, 'Skips building project after installing package', false);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $selectedPackage = $input->getArgument('package');
        if (!$this->hasPackage($selectedPackage)) {
            $this->io->error(sprintf(
                'Unknown package [%s]',
                $selectedPackage
            ));
            exit(1);
        }
        
        $this->io->info('Uninstalling package');
        if (!$input->getOption('keep-files')) {
            $this->removeFiles($selectedPackage);
        }
        $this->removeRequireFromConfig($selectedPackage)
             ->removePackageFromPackagesFile($selectedPackage);
        
        $this->io->info(sprintf(
            'Package [%s] successfully uninstalled.',
            $selectedPackage
        ));

        if (!$input->getOption('skip-build')) {
            $this->getApplication()
                ->find('build')
                ->run(new ArrayInput(array(
                    'command' => 'build'
                )), $output);
        }
    }

    /**
     * @param string $package
     * @return $this
     */
    protected function removeRequireFromConfig($package)
    {
        $this->io->info('Removing package from config...');
        $configFile = $this->getApplication()->getConfigFile();
        $config = $configFile->read();
        if (isset($config['require'][$package])) {
            $this->io->comment(sprintf(
                '  Unset: "%s" => "%s"',
                    $package,
                    $config['require'][$package]
            ));
            unset($config['require'][$package]);
            $config['require'] = (object) $config['require'];
        }
        $configFile->write($config);
        return $this;
    }

    /**
     * @param string $package
     * @return $this
     */
    protected function removePackageFromPackagesFile($package)
    {
        $this->io->info('Removing package from packages.json...');
        $packagesFile = $this->getApplication()->getPackagesFile();
        if ($packagesFile->exists()) {
            $packages = $packagesFile->read();
            if (isset($packages['packages'][$package])) {
                $this->io->comment(sprintf(
                    '  Unset: %s in packages.json',
                    $package
                ));
                unset($packages['packages'][$package]);
            }
        }
        unlink($packagesFile->getPath());
        $packagesFile->write($packages);
        return $this;
    }

    /**
     * @param string $package
     */
    protected function removeFiles($package)
    {
        $this->io->info('Removing downloaded files...');
        foreach ($this->getPackages() as $version) {
            /* @var $version \Composer\Package\Package */
            if ($version->getName() === $package) {
                $filename = preg_replace(
                    '/^' . preg_quote($this->composer->getPackage()->getHomepage(), '/') . '/',
                    $this->getExtraConfig()->getDocroot(),
                    $version->getDistUrl()
                );
                if (file_exists($filename)) {
                    $this->io->comment(sprintf(
                        '  Deleting: %s',
                        $filename
                    ));
                    unlink($filename);
                } else {
                    $this->io->error(sprintf(
                        '   Cannot find dist file [%s]',
                        $filename
                    ));
                }
            }
        }
    }

    /**
     * @param string $package
     * @return bool
     */
    protected function hasPackage($package)
    {
        foreach ($this->composer->getPackage()->getRequires() as $link) {
            if ($package === $link->getTarget()) {
                return true;
            }
        }
        return false;
    }
}