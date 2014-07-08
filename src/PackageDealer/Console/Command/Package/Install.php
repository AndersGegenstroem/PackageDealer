<?php

namespace PackageDealer\Console\Command\Package;

use Composer\Json\JsonFile;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Package\Package;
use Composer\Package\Version\VersionParser;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use PackageDealer\Console\Command\Command;

class Install extends Command
{
    protected function configure()
    {
        $this->setName('package/install')
            ->setDescription('Installs a package.')
            ->addArgument('package', InputArgument::REQUIRED, 'The package name')
            ->addArgument('version', InputArgument::OPTIONAL, 'The version constraint', '*');
    }

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

        $providers = $this->getProviders()->whatProvides(
            $package,
            $versionParser->parseConstraints($version)
        );

        if (empty($providers)) {
            return $this->io->error(sprintf(
                'Cannot find provider for package "%s" with version "%s"',
                $package,
                $version
            ));
        }

        $config = new JsonFile($input->getOption('config'));
        $content = $config->read();

        if (!array_key_exists('require', $content)) {
            $content['require'] = array();
        }

        $content['require'][$package] = $version;

        var_dump($content);
    }

    protected function hasPackage($package)
    {
        foreach ($this->composer->getPackage()->getRequires() as $link) {
            if ($package === $link->getTarget()) {
                return true;
            }
        }
        return false;
    }

    protected function packageExists($package, $version)
    {
        return $this->getProviders()->whatProvides($package, $version);
    }
}