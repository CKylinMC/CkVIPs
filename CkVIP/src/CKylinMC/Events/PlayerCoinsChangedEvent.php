<?php

declare(strict_types=1);

namespace CKylinMC\Events;

use CKylinMC\CkVIP;

class PlayerCoinsChangedEvent extends CkVIPOnceEvent
{
    private $player;
    public function __construct(CkVIP $plugin,string $player)
    {
        parent::__construct($plugin);
        $this->player = $player;
    }
    public function getPlayerName():string{
        return $this->player;
    }
}