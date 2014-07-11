<?php

namespace PackageDealer\Console\Command;

use Composer\IO\ConsoleIO;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PackageDealer\Console\Helper\ConsoleIO as IOHelper;
use PackageDealer\Console\Helper\Provider as ProviderHelper;

class Init extends Command
{
    const DEFAULT_TITLE = 'PackageDealer Composer Repository';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Creates a new packagedealer configuration file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $helper   = $this->getHelperSet();
        $io       = new ConsoleIO($input, $output, $this->getHelperSet());
        $this->io = new IOHelper($io, $this->getHelperSet());

        $helper->set($this->io);
        $helper->set(new ProviderHelper());
    }

    /**
     * @param InputInterface $input The input instance
     * @param OutputInterface $output The output instance
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $this->getConfigFile(
            $input->getOption('config')
        );

        if (empty($configFile)) {
            return null;
        }

        $title = $this->io->ask(sprintf(
            'Please enter the name of this installation: <info>[%s] ',
            self::DEFAULT_TITLE
        ));

        if (empty($title)) {
            $title = self::DEFAULT_TITLE;
        }

        $homepage = $this->getHomepage();
        $docroot  = $this->getDocumentRoot();
        if (empty($docroot)) {
            return null;
        }

        $twig   = $this->getApplication()->getTwig();

        $config = $twig->render('packagedealer.twig.json', array(
            'title'    => $title,
            'homepage' => $homepage,
            'docroot'  => $docroot,
        ));

        file_put_contents($configFile, $config);

        $this->io->info(sprintf(
            'Configfile written to [%s]',
            $configFile
        ));
    }

    /**
     * @param $filename
     * @return null|string
     */
    protected function getConfigFile($filename)
    {
        $configFile = null;

        if (is_file($filename)) {
            $overwrtie = $this->io->ask('Config file already exists! Do you want to overwrite it? ', array('Y','N'));
            if (strtolower($overwrtie) === 'y') {
                $configFile = realpath($filename);
            }
        } elseif (is_dir($filename)) {
            $this->io->info('Config file is a directory! File will be created within that directory using default filename "packagedealer.json".');
            return realpath($filename) . DIRECTORY_SEPARATOR . 'packagedealer.json';
        } elseif(is_dir(dirname($filename))) {
            if (is_writeable(dirname($filename))) {
                $configFile = realpath(dirname($filename)) . DIRECTORY_SEPARATOR . basename($filename);
            } else {
                $this->io->error('Parent directory for config file is not writable.');
            }
        } else {
            $this->io->error('Parent directory for config file could not be found.');
        }

        return $configFile;
    }

    /**
     * @return string
     */
    protected function getHomepage()
    {
        $response = null;
        do {
            $homepage = $this->io->ask('Enter the domain or IP address, where PackageDealer will be accessible: ');

            if (!empty($homepage)) {
                if (!preg_match('/^https[s]*:\/\//', $homepage)) {
                    $homepage = 'http://' . $homepage;
                }

                $response = $homepage;
            }

        } while($response === null);

        return $response;
    }

    /**
     * @return string
     */
    protected function getDocumentRoot()
    {
        $response = null;
        do {
            $docroot = $this->io->ask('Enter the path to the directory, where PackageDealer shall write htdocs files to: ');

            if (!is_dir($docroot)) {
                if (is_dir(dirname($docroot)) && is_writeable(dirname($docroot))) {
                    mkdir($docroot, 0755);
                    $response = realpath($docroot);
                } else {
                    $this->io->error('Provided path is not a directory and cannot be created');
                }
            } elseif (!is_writeable($docroot)) {
                $this->io->error('Provided directory is not writeable!');
            } else {
                $response = realpath($docroot);

                $it = new \DirectoryIterator($docroot);
                foreach ($it as $node) {
                    if (!$it->isDot()) {
                        $this->io->error('Provided directory is not empty!');
                        $response = null;
                        break;
                    }
                }
            }

        } while($response === null);

        return $response;
    }
}