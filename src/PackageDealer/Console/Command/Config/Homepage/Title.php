<?php

namespace PackageDealer\Console\Command\Config\Homepage;

use PackageDealer\Console\Command\Config\Homepage;

class Title extends Homepage
{
    public function configure()
    {
        parent::configure();
        $this->setName('homepage/title')
             ->setDescription('Sets or shows the homepage title');
    }

    protected function get()
    {
        return parent::get()->title;
    }

    protected function set($value)
    {
        parent::get()->title = $value;
    }
}