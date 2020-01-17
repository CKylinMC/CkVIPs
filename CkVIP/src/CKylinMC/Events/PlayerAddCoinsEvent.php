<?php

namespace CKylinMC\Events;

use CKylinMC\CkVIP;

class PlayerAddCoinsEvent extends CkVIPEvent
{
    private $player,$coins;
    public function __construct(CkVIP $plugin,string $player,int $coins)
    {
        parent::__construct($plugin);
        $this->player = $player;
        $this->coins = $coins;
    }
    public function getPlayerName():string{
        return $this->player;
    }
    public function getCoins():int {
        return $this->coins;
    }
//    public function setCoins(int $coins): void
//    {
//        $this->coins = $coins;
//    }
}