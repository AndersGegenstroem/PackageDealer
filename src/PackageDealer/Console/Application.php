<?php
/*
 * COntains the Console Application
 */

namespace PackageDealer\Console;

use PackageDealer\PackageDealer,
    Symfony\Component\Console\Application as BaseApplication,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Formatter\OutputFormatterInterface,
    Symfony\Component\Console\Formatter\OutputFormatterStyle,
    Symfony\Component\Finder\Finder,
    Composer\Util\ErrorHandler;

/**
 * @author Anders Gegenstroem <anders.gegenstroem@googlemail.com>
 */
class Application extends BaseApplication
{
    protected $_formatter = array(
        'question' => 'cyan'
    );

    public function __construct()
    {
        parent::__construct(
            PackageDealer::NAME,
            PackageDealer::VERSION
        );
        
        $this->getDefinition()->addOption(
            new InputOption(
                'config',
                'c',
                InputOption::VALUE_OPTIONAL,
                'The path to the config file to use',
                realpath(__DIR__ . '/../../..') . '/config.json'
            )
        );
        ErrorHandler::register();
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->registerCommands()
             ->setFormatter($output->getFormatter())
             ->getHelperSet()->set(
                 new Helper\ConsoleIO($input, $output, $this->getHelperSet())
             );
        return parent::doRun($input, $output);
    }
    
    private function registerCommands()
    {
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('Command.php')
            ->notName('Repository.php')
            ->notName('Config.php')
            ->notName('Archive.php')
            ->notName('Homepage.php')
            ->in(__DIR__.'/Command');
        
        foreach ($finder as $file) {
            $path = explode(DIRECTORY_SEPARATOR, $file->getPathName());
            $className = array();
            foreach (array_reverse($path) as $current) {
                array_unshift($className, basename($current, '.php'));
                if ($current === 'PackageDealer') {
                    break;
                }
            }
            $className = implode('\\', $className);
            $this->add(new $className());
        }
        return $this;
    }
    
    private function setFormatter(OutputFormatterInterface $formatter)
    {
        foreach ($this->_formatter as $name=>$style) {
            $formatter->setStyle($name, new OutputFormatterStyle($style));
        }
        return $this;
    }
}
