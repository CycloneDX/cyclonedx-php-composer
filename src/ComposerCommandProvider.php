<?php

namespace CycloneDX;

use CycloneDX\Model\Bom;
use CycloneDX\Model\Component;

use Composer\Semver\VersionParser;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ComposerCommandProvider implements CommandProvider
{
    public function getCommands()
    {
        return array(new MakeBomCommand);
    }   
}

class MakeBomCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName("makeBom")
            ->setDescription("Generate a CycloneDX Bill of Materials");

        $this->addOption("outputFile", null, InputOption::VALUE_REQUIRED, "Path to the Output File");
        $this->addOption("excludeDev", null, InputOption::VALUE_NONE, "Exclude Dev Dependencies");
        $this->addOption("excludePlugins", null, InputOption::VALUE_NONE, "Exclude Composer Plugins");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locker = $this->getComposer()->getLocker();

        if (!$locker->isLocked() || !$locker->isFresh()) {
            $output->writeln("[x] Lockfile does not exist or is outdated");
            return;
        }
            
        $lockData = $locker->getLockData();
        $packages = $lockData["packages"];

        if ($input->getOption("excludeDev") !== true) {
            array_merge($packages, $lockData["packages-dev"]);
        } else {
            $output->writeln("[!] Dev dependencies will be excluded");
        }

        $output->writeln("[+] Collecting components...");
        $components = array();
        foreach ($packages as &$package) {
            if ($package["type"] === "composer-plugin" && $input->getOption("excludePlugins") !== false) {
                $output->writeln("[!] Skipping plugin " . $package["name"]);
                continue;
            }

            array_push($components, $this->buildComponent($package));
        }

        $bom = new Bom;
        $bom->setComponents($components);

        $output->writeln("[+] Generating BOM...");
        $bomWriter = new BomWriter;
        $bomXml = $bomWriter->writeBom($bom);
        
        $outputFile = $input->getOption("outputFile") ? $input->getOption("outputFile") : "bom.xml";
        $output->writeln("[+] Writing BOM to " . $outputFile . "...");
        \file_put_contents($outputFile, $bomXml);
    }

    private function buildComponent(array $package)
    {
        $component = new Component;

        $splittedName = \explode("/", $package["name"], 2);
        $splittedNameCount = count($splittedName);
        if ($splittedNameCount == 2) {
            $component->setGroup($splittedName[0]);
            $component->setName($splittedName[1]);
        } else if ($splittedNameCount == 1) {
            $component->setName($splittedName[0]);
        }

        $versionParser = new VersionParser;
        $component->setVersion($versionParser->normalize($package["version"]));
        
        // TODO: Research possible types in composer
        $component->setType("library");

        // TODO: Validate License with SPDX license list
        $component->setLicenses($package["license"]);

        if (\array_key_exists("shasum", $package["dist"]) && $package["dist"]["shasum"]) {
            $component->setHashes(array("sha1" => $package["dist"]["shasum"]));
        }

        // TODO: Find a more robust way to put this together?
        $component->setPackageUrl(\sprintf("pkg://composer/%s/%s@%s", $component->getGroup(), $component->getName(), $component->getVersion()));

        return $component;
    }

}
