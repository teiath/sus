<?php 

namespace SUS\UserBundle\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class YamlUserLoader extends FileLoader
{
    
    private $resource;
    
    public function __construct($resource =null) {
        $this->resource = $resource;
    }
    
    public function load($resource, $type = null)
    {
        $configValues = Yaml::parse(file_get_contents($resource));

        return $configValues;
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo( $resource, PATHINFO_EXTENSION );
    }
}

?>