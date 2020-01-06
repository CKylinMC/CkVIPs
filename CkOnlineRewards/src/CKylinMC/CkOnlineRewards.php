<?php

declare(strict_types=1);

namespace CKylinMC;

use pocketmine\plugin\PluginBase;

class CkOnlineRewards extends PluginBase{
    public function onEnable():void
    {
        $this->getLogger()->info("CkOnlineRewards Enabled.");
    }
    public function onDisable():void
    {
        $this->getLogger()->info("CkOnlineRewards Disabled.");
    }
}
