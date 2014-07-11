<?php

namespace PackageDealer\Console\Command\Provider;

use Composer\Json\JsonFile;
use PackageDealer\Console\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Uninstall extends Command\Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('provider/uninstall')
            ->setDescription('Uninstalls a provider.')
            ->addArgument('provider', InputArgument::REQUIRED, 'The provider url');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->info('Scanning providers...');
        $url = $input->getArgument('provider');

        foreach ($this->composer->getRepositoryManager()->getRepositories() as $repository) {
            if ($url === $this->getHelper('provider')->getUrl($repository)) {
                $match = $repository;
            }
        }

        if (!isset($match)) {
            return $this->io->error('Couldn\'t find installed provider.');
        }

        $this->io->comment('  Provider found.');
        $this->io->info('Scanning provider packages...');

        $uninstallCommand = $this->getApplication()->find('package/uninstall');

        $providerPackages = array();
        foreach ($match->getPackages() as $package) {
            if (!in_array($package->getPrettyName(), $providerPackages)) {
                $providerPackages[] = $package->getPrettyName();
                $uninstallCommand->run(
                    new ArrayInput(array(
                        'command' => 'package/uninstall',
                        'package' => $package->getPrettyName(),
                        '--skip-build' => true
                    )),
                    $output
                );
            }
        }

        $this->io->info('Remove provider from configuration file.');
        $config = new JsonFile($input->getOption('config'));
        $content = $config->read();

        if (!array_key_exists('repositories', $content)) {
            $content['repositories'] = array();
        }
        foreach ($content['repositories'] as $k=>$repository) {
            if (isset($repository['url']) && $repository['url'] === $url) {
                $repoIndex = $k;
                break;
            }
        }

        if (isset($repoIndex)) {
            unset($content['repositories'][$repoIndex]);
        }

        if (isset($content['require']) && !is_object($content['require'])) {
            $content['require'] = (object)$content['require'];
        }

        $config->write($content);

        $this->io->comment('  New configuration file written');

        $this->getApplication()
            ->find('build')
            ->run(new ArrayInput(array(
                'command' => 'build'
            )), $output);
    }
}