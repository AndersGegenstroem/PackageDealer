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
    Composer\Util\ErrorHandler,
    Composer\Json\JsonFile;

/**
 * @author Anders Gegenstroem <anders.gegenstroem@googlemail.com>
 */
class Application extends BaseApplication
{
    protected $_formatter = array(
        'question' => 'cyan',
    );
    /**
     * @var \Composer\Composer
     */
    protected $composer = null;
    /**
     * @var \Composer\Json\JsonFile
     */
    private $configFile = null;
    /**
     * @var \Composer\Json\JsonFile
     */
    private $packagesFile = null;
    
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
                self::getDefaultConfigFilename()
            )
        );
        ErrorHandler::register();
    }

    public function loadConfig($filename=null)
    {
        if (empty($filename)) {
            $filename = self::getDefaultConfigFilename();
        }
        $this->configFile = new JsonFile($filename);
        return $this->configFile->exists();
    }
    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->registerCommands()
             ->setFormatter($output->getFormatter());
        return parent::doRun($input, $output);
    }
    
    private function registerCommands()
    {
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('Command.php')
            ->notName('Provider.php')
            ->notName('Package.php')
            ->in(__DIR__.'/Command');
        
        foreach ($finder as $file) {
            $path = explode(DIRECTORY_SEPARATOR, $file->getPathName());
            $classNameParts = array();
            foreach (array_reverse($path) as $current) {
                array_unshift($classNameParts, basename($current, '.php'));
                if ($current === 'PackageDealer') {
                    break;
                }
            }
            $className = implode('\\', $classNameParts);
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
    /**
     * @return \Composer\Json\JsonFile
     */
    public function getConfigFile()
    {
        return $this->configFile;
    }
    /**
     * @return \Composer\Json\JsonFile
     */
    public function getPackagesFile()
    {
        if ($this->packagesFile === null) {
            $config = $this->getConfigFile()->read();
            $this->packagesFile = new JsonFile($config['extra']['docroot'] . '/packages.json');
        }
        return $this->packagesFile;
    }
    
    private static function getDefaultConfigFilename()
    {
        return realpath(__DIR__ . '/../../..') . '/packagedealer.json';
    }
}
