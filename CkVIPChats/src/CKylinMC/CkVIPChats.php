<?php

declare(strict_types=1);

namespace CKylinMC;

use pocketmine\plugin\PluginBase;

class CkVIPChats extends PluginBase{
    public function onEnable():void
    {
        $this->getLogger()->info("CkVIPChats Enabled.");
    }
    public function onDisable():void
    {
        $this->getLogger()->info("CkVIPChats Disabled.");
    }
}
