<?php

declare(strict_types=1);

namespace CKylinMC;

use pocketmine\plugin\PluginBase;

class CkLoginGift extends PluginBase{
    public function onEnable():void
    {
        $this->getLogger()->info("CkLoginGift Enabled.");
    }
    public function onDisable():void
    {
        $this->getLogger()->info("CkLoginGift Disabled.");
    }
}
