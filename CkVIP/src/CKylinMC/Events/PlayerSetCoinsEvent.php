<?php

declare(strict_types=1);

namespace CKylinMC\Events;

use CKylinMC\CkVIP;

class PlayerSetCoinsEvent extends CkVIPEvent
{
    private $player,$coins,$action;
    public function __construct(CkVIP $plugin,string $player,int $coins, int $action)
    {
        parent::__construct($plugin);
        $this->player = $player;
        $this->coins = $coins;
        $this->action = $action;
    }
    public function getPlayerName():string{
        return $this->player;
    }
    public function getAction():int {
        return $this->action;
    }
    public function getCoins():int {
        return $this->coins;
    }
//    public function setCoins(int $coins): void
//    {
//        $this->coins = $coins;
//    }
}