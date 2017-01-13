<?php

namespace PackageDealer\Console\Command\Package;

use Composer\Json\JsonFile;
use Composer\Package\Version\VersionParser;
use Composer\Repository\PlatformRepository;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use PackageDealer\Console\Command;

class Install extends Command\Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('package/install')
            ->setDescription('Installs a package.')
            ->addArgument('package', InputArgument::REQUIRED, 'The package name')
            ->addArgument('version', InputArgument::OPTIONAL, 'The version constraint', '*')
            ->addOption('skip-build', false, InputOption::VALUE_OPTIONAL, 'Skips building project after installing package', false);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $package = $input->getArgument('package');
        if ($this->hasPackage($package)) {
            $this->io->error(sprintf(
                'Package [%s] already registered. If you want to update the version constraint, un- and reinstall the package.',
                $package
            ));
            return null;
        }
        $version = $input->getArgument('version');

        $versionParser = new VersionParser();

        $this->io->info('Scanning providers...');
        $providers = $this->getProviders()->whatProvides(
            $package,
            $versionParser->parseConstraints($version),
            true
        );

        if (empty($providers)) {
            return $this->io->error(sprintf(
                'Cannot find provider for package "%s" with version "%s"',
                $package,
                $version
            ));
        }
        $this->io->info('Provider found.');

        $config = new JsonFile($input->getOption('config'));
        $content = $config->read();

        if (!array_key_exists('require', $content)) {
            $content['require'] = array();
        }

        $content['require'][$package] = $version;
        $content['require'] = $this->getDependenciesRecursive($providers, $content['require']);

        $this->io->info('Add package to configuration file.');
        $config->write($content);
        $this->io->info('  New configuration file written...');

        if (!$input->getOption('skip-build')) {
            $buildInput = new ArrayInput([
                'command' => 'build',
            ]);
            $buildInput->setInteractive(false);

            $this->getApplication()
                ->find('build')
                ->run($buildInput, $output);
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

    /**
     * @param string $package
     * @param string $version
     * @return array
     */
    protected function packageExists($package, $version)
    {
        return $this->getProviders()->whatProvides($package, $version);
    }

    protected function getDependenciesRecursive(array $packages, array $dependencies=array())
    {
        foreach ($packages as $package) {
            foreach ($package->getRequires() as $link) {
                $linkName = $link->getTarget();
                if (!preg_match(PlatformRepository::PLATFORM_PACKAGE_REGEX, $linkName) &&
                    !array_key_exists($linkName, $dependencies)
                ) {
                    $dependencies[$linkName] = '*';
                    $providers = $this->getProviders()->whatProvides($linkName, $link->getConstraint());

                    $dependencies = $this->getDependenciesRecursive(
                        $providers,
                        $dependencies
                    );
                }
            }
        }

        return $dependencies;
    }
}