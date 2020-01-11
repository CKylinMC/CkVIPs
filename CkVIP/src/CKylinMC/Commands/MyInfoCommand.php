<?php

namespace CKylinMC\Commands;

use CKylinMC\CkVIP;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\command\PluginCommand;
use pocketmine\plugin\Plugin;

class MyInfoCommand extends PluginCommand
{
    public function __construct()
    {
        $api = CkVIP::$API;
        parent::__construct('myinfo', $api);
        $this->setUsage($api->m('cmd-myinfo-usage'));
        $this->setDescription($api->m('cmd-myinfo-description'));
        $this->setPermission('ckvipcore.cmd.myinfo');
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if(!parent::execute($sender,$commandLabel,$args)){
            return false;
        }
        $api = CkVIP::$API;
        $usermgr = $api->getUserMgr();
        $pn = $sender->getName();
        $p = $api->getServer()->getPlayer($pn);
        if($p===null || !$usermgr->hasUser($pn)) {
            $sender->sendMessage($api->m('player-not-exist',$pn));
            return false;
        }
        $info = $usermgr->getUser($pn);
        if($info===[]){
            $sender->sendMessage($api->m('player-not-exist',$pn));
            return false;
        }
        $lvs = $api->getVIPAvaiableLevels();
        if(!in_array($info['viplevel'], $lvs)){
            $sender->sendMessage($api->m('viplevel-invalid',$info['viplevel']));
            return false;
        }
        $txt[] = $api->m('info-title');
        $txt[] = $api->m('player').': '.$pn;
        $txt[] = $api->m('coin').': '.$info['coins'];
        $txt[] = $api->m('viplevel').': '.$lvs[$info['viplevel']];
        $txt[] = $api->m('expire').': '.$usermgr->dateStr($info['expire']) . '('.($usermgr->isUserVIPAvailable($pn)?$api->m('valid'):$api->m('invalid')).')';
        $txt[] = $api->m('reallevel').': '.$lvs[$usermgr->getUserAvailableVIPLevel($pn)];
        $txt[] = $api->m('status').': '.$api->getStatusText($info['status']);
        foreach($txt as $t){
            $sender->sendMessage($t);
        }
        return true;
    }
}