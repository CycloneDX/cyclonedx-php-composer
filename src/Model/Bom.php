<?php

namespace CycloneDX;


class BOM 
{
    /**
     * @var array
     */
    private $components;

    public function getComponents() 
    {
        return $this->components;
    }

    public function setComponents(array $components) 
    {
        $this->components = $components;
    }

}
