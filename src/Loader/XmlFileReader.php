<?php

namespace App\Loader;

use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Config\Loader\FileLoader;

class XmlFileReader extends FileLoader {
    public $validFile=true;

    /**
     * Read the content of an XML file.
     *
     * @param string $type The resource type
     * @return array.
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);
        try {
            $dom = XmlUtils::loadFile($path);
        }
        catch (\InvalidArgumentException $e) {
            $this->validFile=false;
            return $e; 
            //throw new \InvalidArgumentException(sprintf('Unable to parse file "%s".', $file), $e->getCode(), $e);
        }
        $arrayXml = XmlUtils::convertDomElementToArray($dom->documentElement);
        return $arrayXml;

    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed $resource A resource
     * @param string $type The resource type
     *
     * @return bool    true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'xml' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}