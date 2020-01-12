<?php

declare(strict_types=1);

namespace CKylinMC\Events;

use CKylinMC\CkVIP;
use pocketmine\event\plugin\PluginEvent;

class CkVIPOnceEvent extends PluginEvent{
    public function __construct(CkVIP $plugin)
    {
        parent::__construct($plugin);
    }
}
