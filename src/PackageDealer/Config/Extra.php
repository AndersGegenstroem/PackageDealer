<?php

namespace PackageDealer\Config;

class Extra
{
    protected $data = null;
    
    public function __construct(\Composer\Package\RootPackage $package)
    {
        $this->data = $package->getExtra();
    }
    
    public function getDocroot()
    {
        return $this->get('docroot');
    }
    
    public function getArchivePath()
    {
        return $this->get('archive-path');
    }
    
    public function getArchiveType()
    {
        return $this->get('archive-type');
    }
    
    protected function get($name, $default=null)
    {
        if (!array_key_exists($name, $this->data)) {
            $this->data[$name] = $default;
        }
        return $this->data[$name];
    }
}