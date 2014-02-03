<?php

namespace PackageDealer\Console\Command\Config;

use PackageDealer\Console\Command\Config;

abstract class Archive extends Config
{
    protected function get()
    {
        return $this->config->archive;
    }
}