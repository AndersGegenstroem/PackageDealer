<?php

namespace PackageDealer\Config;

use Iterator;

class DynamicContainer extends Container implements Iterator
{
    public function apply(array $values)
    {
        foreach ($values as $name=>$options) {
            $this->_data[$name] = $options;
        }
    }

    public function current()
    {
        return current($this->_data);
    }

    public function key()
    {
        return key($this->_data);
    }

    public function next()
    {
        next($this->_data);
    }

    public function rewind()
    {
        reset($this->_data);
    }

    public function valid()
    {
        return array_key_exists($this->key(), $this->_data);
    }
}