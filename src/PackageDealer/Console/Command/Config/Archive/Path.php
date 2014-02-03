<?php

namespace PackageDealer\Console\Command\Config\Archive;

use PackageDealer\Console\Command\Config\Archive;

class Path extends Archive
{
    public function configure()
    {
        parent::configure();
        $this->setName('archive/path')
             ->setDescription('Sets or shows the archive path');
    }

    protected function get()
    {
        return parent::get()->path;
    }

    protected function set($value)
    {
        parent::get()->path = $value;
    }
}