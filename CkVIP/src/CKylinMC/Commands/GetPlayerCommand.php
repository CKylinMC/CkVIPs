<?php

declare(strict_types=1);

namespace CKylinMC\Commands;

use CKylinMC\CkVIP;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

class GetPlayerCommand extends PluginCommand
{
    public function __construct()
    {
        $api = CkVIP::$API;
        parent::__construct('getpl', $api);
        $this->setUsage($api->m('cmd-getpl-usage'));
        $this->setDescription($api->m('cmd-getpl-description'));
        $this->setPermission('ckvipcore.cmd.getpl');
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if(!parent::execute($sender,$commandLabel,$args)){
            return false;
        }
        if(count($args)!==1){
            $sender->sendMessage($this->getUsage());
            return false;
        }
        $api = CkVIP::$API;
        $usermgr = $api->getUserMgr();
        $pn = $args[0];
//        $p = $api->getServer()->getPlayer($pn);
        if(!$usermgr->hasUser($pn)) {
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
        $txt[] = $api->m('viplevel').': '.$usermgr->getVIPLevelName($info['viplevel']);
        $txt[] = $api->m('expire').': '.$usermgr->dateStr($info['expire']) . '('.($usermgr->isUserVIPAvailable($pn)?$api->m('valid'):$api->m('invalid')).')';
        $txt[] = $api->m('reallevel').': '.$usermgr->getVIPLevelName($usermgr->getUserAvailableVIPLevel($pn));
        $txt[] = $api->m('status').': '.$api->getStatusText($info['status']);
        foreach($txt as $t){
            $sender->sendMessage($t);
        }
        return true;
    }
}