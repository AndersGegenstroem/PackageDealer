<?php

namespace PackageDealer\Console\Command\Config\Homepage;

use PackageDealer\Console\Command\Config\Homepage;

class Docroot extends Homepage
{
    public function configure()
    {
        parent::configure();
        $this->setName('homepage/docroot')
             ->setDescription('Sets or shows the homepage document root');
    }

    protected function get()
    {
        return parent::get()->docroot;
    }

    protected function set($value)
    {
        parent::get()->docroot = $value;
    }
}