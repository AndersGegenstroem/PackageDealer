<?php

namespace PackageDealer\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Factory;
use Symfony\Component\Finder\Finder;

class Build extends Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('build')
             ->setDescription('Scans repositories, writes archive files and creates webpage');
    }

    /**
     * @param InputInterface $input The input instance
     * @param OutputInterface $output The output instance
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pool = $this->getRepositoryPool();
        $rootPackage = $this->composer->getPackage();
        
        $this->io->info('Building "PackageDealer"...');
        
        $extra = $this->getExtraConfig();
        if (!is_dir($extra->getDocroot())) {
            mkdir($extra->getDocroot(), '0777', true);
            $this->io->comment(sprintf(
                '  Created DocumentRoot at [%s]',
                $extra->getDocroot()
            ));
        }
        $archiveDir = $extra->getDocroot() . DIRECTORY_SEPARATOR . $extra->getArchivePath();
        if (!is_dir($archiveDir)) {
            mkdir($archiveDir, '0777', true);
            $this->io->comment(sprintf(
                '  Created Archive directory at [%s]',
                $archiveDir
            ));
        }
        $archiveType = $extra->getArchiveType();
        $archiveUrl = $rootPackage->getHomepage() . '/' . $extra->getArchivePath() . '/';
        
        $archiver = $this->getArchiveManager();
        $archiver->setOverwriteFiles(false);
        
        $requires = $rootPackage->getRequires();
        $packageStack = array();
        foreach ($requires as $req) {
            $this->io->info(sprintf(
                '  [%s]',
                $req->getTarget()
            ));
            $providers = $pool->whatProvides(
                $req->getTarget(),
                $req->getConstraint()
            );
            if (empty($providers)) {
                $this->io->error(sprintf(
                    'Couldn\'t find provider!'
                ));
            }
            
            foreach ($providers as $package) {
                $exists = realpath(
                    $archiveDir .
                    DIRECTORY_SEPARATOR .
                    $archiver->getPackageFilename($package) .
                    '.' .
                    $archiveType
                );
                /* @var $package \Composer\Package\CompletePackage */
                $path = $archiver->archive($package, $archiveType, $archiveDir);
                
                if (!$exists) {
                    $this->io->comment(sprintf(
                        '    Dumping: %s',
                        basename($path)
                    ));
                }
                
                $package->setDistType($archiveType);
                $package->setDistUrl($archiveUrl . basename($path));
                $package->setDistSha1Checksum(hash_file('sha1', $path));
                $package->setDistReference($package->getSourceReference());
                $packageStack[] = $package;
            }
        }
        
        $this->dumpPackages($packageStack, $extra->getDocroot() . '/packages.json');
        $this->dumpWebpage($packageStack);
        
        $this->io->info('Build successful!');
    }

    /**
     * @param array $packages
     * @param string $filename
     */
    private function dumpPackages(array $packages, $filename)
    {
        $this->io->info('Dumping packages.json...');
        $dump = array('packages'=>array());
        $dumper = new \Composer\Package\Dumper\ArrayDumper();
        foreach ($packages as $package) {
            $dump['packages'][$package->getPrettyName()][$package->getPrettyVersion()] = $dumper->dump($package);
        }
        
        if (is_file($filename)) {
            unlink($filename);
        }
        $file = new \Composer\Json\JsonFile($filename);
        $file->write($dump);
        $this->io->comment('  Packages.json written');
    }


    private function dumpWebpage(array $packages)
    {
        $this->io->info('Dumping web view...');

        $templateDir = $this->getApplication()->getTemplateDir();
        $docroot = $this->getExtraConfig()->getDocroot();

        $finder = new Finder();
        $finder->files()
            ->name('*.css')
            ->name('*.js')
            ->in($templateDir);

        foreach ($finder as $file) {
            /* @var $file \Symfony\Component\Finder\SplFileInfo */
            $dir = dirname($file->getRelativePathName());
            if (!is_dir($docroot . DIRECTORY_SEPARATOR . $dir)) {
                mkdir($docroot . DIRECTORY_SEPARATOR . $dir, 0755, true);
            }

            copy($file->getPathname(), $docroot . DIRECTORY_SEPARATOR . $file->getRelativePathName());
        }

        $twig = $this->getApplication()->getTwig();
        $rootPackage = $this->composer->getPackage();

        $viewPackages = array();
        foreach ($packages as $package) {
            /* @var $package \Composer\Package\PackageInterface */
            $viewPackages[] = array(
                'name' => $package->getName(),
                'version' => $package->getPrettyVersion()
            );
        }

        file_put_contents(
            $docroot . DIRECTORY_SEPARATOR. 'index.html',
            $twig->render('index.twig.html', array(
                'title' => $rootPackage->getPrettyName(),
                'description' => $rootPackage->getDescription(),
                'packages' => json_encode($viewPackages)
            ))
        );

        $this->io->comment('  Files written.');
    }
    /**
     * @return \Composer\Package\Archiver\ArchiveManager
     */
    protected function getArchiveManager()
    {
        $factory = new Factory();
        return $factory->createArchiveManager(
            $this->composer->getConfig(),
            $this->composer->getDownloadManager()
        );
    }

    /**
     * @return \Composer\DependencyResolver\Pool
     */
    protected function getRepositoryPool()
    {
        $pool = new \Composer\DependencyResolver\Pool(
            $this->composer->getPackage()->getMinimumStability()
        );
        foreach ($this->composer->getRepositoryManager()->getRepositories() as $repo) {
            $pool->addRepository($repo);
        }
        return $pool;
    }
}
