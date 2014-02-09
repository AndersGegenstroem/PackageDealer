<?php

namespace PackageDealer\Console\Helper;

use Symfony\Component\Console\Helper\Helper,
    Composer\Repository\ArrayRepository,
    ReflectionClass;

class Provider extends Helper
{
    public function getName()
    {
        return 'provider';
    }
    
    public function getUrl(ArrayRepository $repository)
    {
        $class = new ReflectionClass($repository);
        $property = $class->getProperty('url');
        $property->setAccessible(true);
        return $property->getValue($repository);
    }
    
    public function getType(ArrayRepository $repository)
    {
        return strtolower(
            preg_replace(
                '/^(.*)\\\\(.*)Repository$/',
                '\2',
                get_class($repository)
            )
        );
    }
}