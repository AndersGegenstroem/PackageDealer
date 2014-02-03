<?php

namespace PackageDealer\Console\Command\Config;

use PackageDealer\Console\Command\Config;

abstract class Homepage extends Config
{
    protected function get()
    {
        return $this->config->homepage;
    }
}