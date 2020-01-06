<?php

declare(strict_types=1);

namespace CKylinMC;

use pocketmine\plugin\PluginBase;

class CkVIP extends PluginBase{
    public function onEnable():void
    {
        $this->getLogger()->info("CkVIP Enabled.");
    }
    public function onDisable():void
    {
        $this->getLogger()->info("CkVIP Disabled.");
    }
}
