<?php

namespace CKylinMC\Events;

use CKylinMC\CkVIP;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\event\Cancellable;

class CkVIPEvent extends PluginEvent implements Cancellable{
    public function __construct(CkVIP $plugin)
    {
        parent::__construct($plugin);
    }
}
