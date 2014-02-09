<?php

namespace PackageDealer\Console\Command\Package;

use Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    PackageDealer\Console\Command\Command,
    Composer\Json\JsonFile,
    Composer\Repository\ComposerRepository,
    Composer\Package\Package;

class Show extends Command
{
    protected $versions = array();
    
    protected $packages = array();
    
    protected function configure()
    {
        $this->setName('package/list')
             ->setDescription('Shows all registered packages.')
             ->addArgument('package', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'The package name');
    }
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $packageFile = new JsonFile(
            $this->getExtraConfig()->getDocroot() . DIRECTORY_SEPARATOR . 'packages.json'
        );
        if ($packageFile->exists()) {
            $packages = $packageFile->read();
            if (array_key_exists('packages', $packages)) {
                $selectedPackage = $input->getArgument('package');
                foreach ($packages['packages'] as $name=>$versions) {
                    if (!empty($selectedPackage) && $selectedPackage !== $name) {
                        continue;
                    }
                    if (!array_key_exists($name, $this->versions)) {
                        $this->versions[$name] = array();
                    }
                    $this->versions[$name] = array_merge(
                        $this->versions[$name],
                        array_keys($versions)
                    );
                    
                    foreach ($versions as $version) {
                        $this->packages[] = new Package(
                            $version['name'],
                            $version['version_normalized'],
                            $version['version']
                        );
                    }
                }
            }
        }
    }
    
    private function addPackageProvider(array &$stack, \Composer\Package\Package $package, \Composer\Repository\RepositoryInterface $provider, $require)
    {
        $packageName  = $package->getName();
        $providerName = $this->getHelper('provider')->getUrl($provider);
        if (!array_key_exists($packageName, $stack)) {
            $stack[$packageName] = array(
                'require' => $require,
                'versions' => array(),
            );
        }
        $stack[$packageName]['versions'][] = array($package->getVersion(), $providerName);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packages = array();
        $links = array();
        foreach ($this->composer->getPackage()->getRequires() as $link) {
            /* @var $link \Composer\Package\Link */
            $links[$link->getTarget()] = $link->getPrettyConstraint();
        }
        foreach ($this->packages as $package) {
            /* @var $package \Composer\Package\Package */
            foreach ($this->composer->getRepositoryManager()->getRepositories() as $provider) {
                if ($provider instanceof ComposerRepository) {
                    $provides = $provider->whatProvides($this->getProviders(), $package->getName(), $package->getVersion());
                    if (!empty($provides)) {
                        $this->addPackageProvider($packages, $package, $provider, $links[$package->getName()]);
                    }
                } elseif ($provider->hasPackage($package)) {
                    $this->addPackageProvider($packages, $package, $provider, $links[$package->getName()]);
                }
            }
        }
        
        $table = $this->getHelper('table');
        $table->setHeaders(array('Version', 'Provider'));
        foreach ($packages as $name=>$values) {
            $table->setRows(array());
            $this->io->info('');
            $output->writeln(sprintf(
                '<question>%s</question> [<comment>%s</comment>]',
                $name,
                $values['require']
            ));
            
            $table->addRows($values['versions']);
            $table->render($output);
        }
    }
    
    protected function getInstalledVersions($package)
    {
        return array_key_exists($package, $this->versions)
            ? $this->versions[$package]
            : array();
    }
}