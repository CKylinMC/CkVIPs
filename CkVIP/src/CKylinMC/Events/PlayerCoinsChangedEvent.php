<?php

namespace CKylinMC\Events;

use CKylinMC\CkVIP;

class PlayerCoinsChangedEvent extends CkVIPEvent
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