<?php 

namespace CycloneDX\Model;

class License
{
    /**
     * @var string
     */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

}