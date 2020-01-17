<?php

namespace CKylinMC\Events;

use CKylinMC\CkVIP;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\event\Cancellable;

class CkVIPEvent extends PluginEvent implements Cancellable{
    public static $handlerList = null;
    public static $eventPool = [];
    public static $nextEvent = 0;
    public function __construct(CkVIP $plugin)
    {
        parent::__construct($plugin);
    }
}
