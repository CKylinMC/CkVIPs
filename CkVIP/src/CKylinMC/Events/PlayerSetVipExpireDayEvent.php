<?php

namespace CKylinMC\Events;

use CKylinMC\CkVIP;

class PlayerSetVipExpireDayEvent extends CkVIPEvent
{
    private $player,$d;
    public function __construct(CkVIP $plugin,string $player,int $d)
    {
        parent::__construct($plugin);
        $this->player = $player;
        $this->d = $d;
    }
    public function getPlayerName():string{
        return $this->player;
    }
    public function getExpireDay():int {
        return $this->d;
    }
    public function setExpireDay(int $d): void
    {
        $this->d = $d;
    }
}