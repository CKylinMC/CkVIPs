<?php

declare(strict_types=1);

namespace CKylinMC\Events;

use CKylinMC\CkVIP;

class PlayerCreateEvent extends CkVIPEvent
{
    private $player,$config;
    public function __construct(CkVIP $plugin,string $player,array $config)
    {
        parent::__construct($plugin);
        $this->player = $player;
        $this->config = $config;
    }
    public function getPlayerName():string{
        return $this->player;
    }
    public function getConfig():array {
        return $this->config;
    }
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
}