<?php

namespace PackageDealer\Config;

use RuntimeException;

class Config extends Container
{
    protected $_path = '';
    
    public function __construct($path)
    {
        parent::__construct(array(
            'homepage' => new Container(array(
                'title' => '',
                'description' => '',
                'docroot' => '',
            )),
            'repositories' => new DynamicContainer(),
            'archive' => new Container(array(
                'path' => 'dist',
                'type' => 'tar',
            )),
        ));
        
        $this->_path = $path;
        if ($this->isReadable()) {
            $this->read();
        }
    }
    
    public function read()
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Config file not readable.');
        }
        $data = @json_decode(file_get_contents($this->_path), true);
        if (!is_array($data)) {
            throw new RuntimeException('Cannot read config file. Malformed content.');
        }
        $this->apply($data);
    }
    
    public function write()
    {
        return file_put_contents($this->_path, json_encode($this->toArray()));
    }
    
    public function getPath()
    {
        return $this->_path;
    }
    
    public function isReadable()
    {
        return $this->exists() && is_readable($this->_path);
    }
    
    public function isWriteable()
    {
        return $this->exists()
            ? is_writeable($this->_path)
            : is_writeable(dirname($this->_path));
    }
    
    public function exists()
    {
        return is_file($this->_path);
    }
}