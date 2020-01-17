<?php


namespace CKylinMC\Listeners;

use CKylinMC\CkVIP;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class PlayerJoinListener implements Listener
{
    private $CkVIP;
    public function __construct(CkVIP $CkVIP)
    {
        $this->CkVIP = $CkVIP;
    }

    public function getPlugin():CkVIP{
        return $this->CkVIP;
    }
    public function onPlayerJoin(PlayerJoinEvent $event):void {
        $p = $event->getPlayer();
        $pn = $p->getName();
        $u = $this->CkVIP->getUserMgr();
        if($u->createUser($pn)===$u::OK) {
            $this->CkVIP->getLogger()->info($this->CkVIP->m('player-profile-created', $pn));
        }
    }
}