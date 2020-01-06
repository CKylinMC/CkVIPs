<?php

declare(strict_types=1);

namespace CKylinMC;

use pocketmine\plugin\PluginBase;

class CkPointCard extends PluginBase{
    public function onEnable():void
    {
        $this->getLogger()->info("CkPointCard Enabled.");
    }
    public function onDisable():void
    {
        $this->getLogger()->info("CkPointCard Disabled.");
    }
}
