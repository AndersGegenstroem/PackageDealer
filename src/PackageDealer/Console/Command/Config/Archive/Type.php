<?php

namespace PackageDealer\Console\Command\Config\Archive;

use PackageDealer\Console\Command\Config\Archive;

class Type extends Archive
{
    public function configure()
    {
        parent::configure();
        $this->setName('archive/type')
             ->setDescription('Sets or shows the archive type');
    }

    protected function get()
    {
        return parent::get()->type;
    }

    protected function set($value)
    {
        parent::get()->type = $value;
    }
}