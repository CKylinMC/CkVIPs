<?php

namespace CKylinMC\Commands;

use CKylinMC\CkVIP;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\command\PluginCommand;
use pocketmine\plugin\Plugin;

class SetPlayerCommand extends PluginCommand
{
    public function __construct()
    {
        $api = CkVIP::$API;
        parent::__construct('setpl', $api);
        $this->setUsage($api->m('cmd-setpl-usage'));
        $this->setDescription($api->m('cmd-setpl-description'));
        $this->setPermission('ckvipcore.cmd.setpl');
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if(!parent::execute($sender,$commandLabel,$args)){
            return false;
        }
        if(count($args)<2){
            $sender->sendMessage($this->getUsage());
            return false;
        }
        $api = CkVIP::$API;
        $usermgr = $api->getUserMgr();
        $pn = array_shift($args);
        $p = $api->getServer()->getPlayer($pn);
        if($p===null || !$usermgr->hasUser($pn)) {
            $sender->sendMessage($api->m('player-not-exist',$pn));
            return false;
        }
        $changes = [];
        foreach ($args as $item){
            $kg = explode('=',$item);
            if(count($kg)!==2||$kg[0]===''||$kg[1]===''){
                $sender->sendMessage($api->m('param-invalid',$kg[0]));
                return false;
            }
            if(!is_numeric($kg[1])){
                $sender->sendMessage($api->m('param-invalid',$kg[0]));
                return false;
            }
            $kg[1] = (int) $kg[1];
            switch ($kg[0]){
                case 'c':
                case 'coin':
                case 'coins':
                    if($kg[1]<0){
                        $sender->sendMessage($api->m('param-invalid',$kg[0]));
                        return false;
                    }
                    $changes['coins'] = $kg[1];
                    break;
                case 'v':
                case 'lv':
                case 'vip':
                case 'level':
                case 'levels':
                case 'viplevel':
                case 'viplevels':
                    if($kg[1]<0){
                        $sender->sendMessage($api->m('param-invalid',$kg[0]));
                        return false;
                    }
                    $changes['viplevel'] = $kg[1];
                    break;
                case 'd':
                case 'day':
                case 'days':
                case 'expire':
                    if($kg[1]<0){
                        $sender->sendMessage($api->m('param-invalid',$kg[0]));
                        return false;
                    }
                    $changes['expire'] = strtotime("+ {$kg[1]} day");
                    break;
            }
        }
        if($usermgr->setUser($pn,$changes)===$usermgr::OK){
            $sender->sendMessage($api->m('cmd-success'));
            return true;
        }

        $sender->sendMessage($api->m('cmd-failed'));
        return false;
    }
}