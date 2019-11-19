<?php 

namespace CycloneDX;

class Component 
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $group;

    /**
     * @var string
     */
    private $publisher;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $description;

    /**
     * @var array
     */
    private $licenses;

    /**
     * @var string
     */
    private $packageUrl;

    /**
     * @var array
     */
    private $hashes;

    public function getName() {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function getGroup() {
        return $this->group;
    }

    public function setGroup(string $group) {
        $this->group = $group;
    }

    public function getPublisher() {
        return $this->publisher;
    }

    public function setPublisher(string $publisher) {
        $this->publisher = $publisher;
    }

    public function getType() {
        return $this->type;
    }

    public function setType(string $type) {
        $this->type = $type;
    }

    public function getVersion() {
        return $this->version;
    }

    public function setVersion(string $version) {
        $this->version = $version;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription(string $description) {
        $this->description = $description;
    }

    public function getLicenses() {
        return $this->licenses;
    }

    public function setLicenses(array $licenses) {
        $this->licenses = $licenses;
    }

    public function getPackageUrl() {
        return $this->packageUrl;
    }

    public function setPackageUrl(string $packageUrl) {
        $this->packageUrl = $packageUrl;
    }

    public function getHashes() {
        return $this->hashes;
    }

    public function setHashes(array $hashes) {
        $this->hashes = $hashes;
    }

}