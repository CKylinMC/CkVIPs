<?php

declare(strict_types=1);

namespace CKylinMC;

use pocketmine\plugin\PluginBase;

class CkVIPEffects extends PluginBase{
    public function onEnable():void
    {
        $this->getLogger()->info("CkVIPEffects Enabled.");
    }
    public function onDisable():void
    {
        $this->getLogger()->info("CkVIPEffects Disabled.");
    }
}
