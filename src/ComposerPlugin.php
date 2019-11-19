<?php

namespace CycloneDX;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;


class ComposerPlugin implements PluginInterface, Capable
{
    public function activate(Composer $composer, IOInterface $io) 
    {

    }

    public function getCapabilities()
    {
        return array(
            'Composer\Plugin\Capability\CommandProvider' => 'CycloneDX\ComposerCommandProvider',
        );
    }
}
