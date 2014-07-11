<?php

namespace PackageDealer\Console\Command\Provider;

use Composer\Json\JsonFile;
use PackageDealer\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Install extends Command\Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('provider/install')
            ->setDescription('Installs a provider.')
            ->addArgument('provider', InputArgument::REQUIRED, 'The provider url');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->info('Installing provider...');

        $url = $input->getArgument('provider');

        $repoManager = $this->composer->getRepositoryManager();
        foreach ($repoManager->getRepositories() as $repository) {
            if ($this->getHelper('provider')->getUrl($repository) === $url) {
                return $this->io->error('Provider already installed.');
            }
        }

        $config = array(
            'url' => $url,
        );

        $provider = $repoManager->createRepository('vcs', $config);
        try {
            $provider->getPackages();

            $this->io->comment(sprintf(
                '%s  Provider found (%u packages)',
                PHP_EOL,
                count($provider)
            ));

            $this->io->info('Add provider to configuration file.');
            $config = new JsonFile($input->getOption('config'));
            $content = $config->read();

            if (!array_key_exists('repositories', $content)) {
                $content['repositories'] = array();
            }
            $content['repositories'][] = array(
                'type' => 'vcs',
                'url' => $url,
            );

            if (isset($content['require']) && !is_object($content['require'])) {
                $content['require'] = (object)$content['require'];
            }

            $config->write($content);

            $this->io->comment('  New configuration file written');

        } catch(\Exception $e) {
            $this->io->error('Could not read packages from provider.');
        }
    }
}