<?php
/*
 * COntains the Console Application
 */

namespace PackageDealer\Console;

use PackageDealer\PackageDealer;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Finder\Finder;
use Composer\Util\ErrorHandler;
use Composer\Json\JsonFile;

/**
 * @author Anders Gegenstroem <anders.gegenstroem@googlemail.com>
 */
class Application extends BaseApplication
{
    /**
     * @var array
     */
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

    /**
     * @return void
     */
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

    /**
     * @param string $filename
     * @return bool
     */
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

    /**
     * @return $this
     */
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

    /**
     * @param OutputFormatterInterface $formatter
     * @return $this
     */
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

    /**
     * @return string
     */
    private static function getDefaultConfigFilename()
    {
        return getcwd() . DIRECTORY_SEPARATOR . 'packagedealer.json';
    }
}
