<?php

namespace PackageDealer\Config;

class Container
{
    protected $_data = null;
    
    public function __construct(array $data=array())
    {
        $this->_data = $data;
    }
    
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new \OutOfBoundsException(
                'Unknown option [' . $name . ']'
            );
        }
        return $this->_data[$name];
    }
    
    public function set($name, $value)
    {
        if (!$this->has($name)) {
            throw new \OutOfBoundsException(
                'Unknown option [' . $name . ']'
            );
        }
        $this->_data[$name] = $value;
        return $this;
    }
    
    public function has($name)
    {
        return array_key_exists($name, $this->_data);
    }
    
    public function apply(array $values)
    {
        foreach ($values as $name=>$value) {
            if ($this->has($name)) {
                if ($this->_data[$name] instanceof Container) {
                    $this->_data[$name]->apply($value);
                } else {
                    $this->set($name, $value);
                }
            }
        }
    }
    
    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->get($name);
        }
    }
    
    public function __set($name, $value)
    {
        if ($this->has($name)) {
            if ($this->_data[$name] instanceof Container) {
                $this->_data[$name]->apply($value);
            } else {
                $this->set($name, $value);
            }
        }
    }
    
    public function toArray()
    {
        $array = array();
        foreach ($this->_data as $name=>$value) {
            if ($value instanceof Container) {
                $value = $value->toArray();
            }
            $array[$name] = $value;
        }
        return $array;
    }
}