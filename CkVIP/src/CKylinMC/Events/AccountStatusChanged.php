<?php

namespace CKylinMC\Events;

use CKylinMC\CkVIP;

class AccountStatusChanged extends CkVIPEvent
{
    private $player,$status;
    public function __construct(CkVIP $plugin,string $player,int $status)
    {
        parent::__construct($plugin);
        $this->player = $player;
        $this->status = $status;
    }
    public function getPlayerName():string{
        return $this->player;
    }
    public function getStatus():int {
        return $this->status;
    }
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }
}