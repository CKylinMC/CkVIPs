<?php

declare(strict_types=1);

namespace CKylinMC;

use pocketmine\plugin\PluginBase;

class CkVIPLoginTips extends PluginBase{
    public function onEnable():void
    {
        $this->getLogger()->info("CkVIPLoginTips Enabled.");
    }
    public function onDisable():void
    {
        $this->getLogger()->info("CkVIPLoginTips Disabled.");
    }
}
