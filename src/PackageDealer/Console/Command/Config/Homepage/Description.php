<?php

namespace PackageDealer\Console\Command\Config\Homepage;

use PackageDealer\Console\Command\Config\Homepage;

class Description extends Homepage
{
    public function configure()
    {
        parent::configure();
        $this->setName('homepage/description')
             ->setDescription('Sets or shows the homepage description');
    }

    protected function get()
    {
        return parent::get()->description;
    }

    protected function set($value)
    {
        parent::get()->description = $value;
    }
}