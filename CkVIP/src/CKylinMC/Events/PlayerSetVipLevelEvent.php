<?php

declare(strict_types=1);

namespace CKylinMC\Events;

use CKylinMC\CkVIP;

class PlayerSetVipLevelEvent extends CkVIPEvent
{
    private $player,$lv;
    public function __construct(CkVIP $plugin,string $player,int $lv)
    {
        parent::__construct($plugin);
        $this->player = $player;
        $this->lv = $lv;
    }
    public function getPlayerName():string{
        return $this->player;
    }
    public function getLevel():int {
        return $this->lv;
    }
//    public function setLevel(int $lv): void
//    {
//        $this->lv = $lv;
//    }
}