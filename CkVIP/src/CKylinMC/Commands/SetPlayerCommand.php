<?php

declare(strict_types=1);

namespace CKylinMC\Commands;

use CKylinMC\CkVIP;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

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
//        $p = $api->getServer()->getPlayer($pn);
        if(!$usermgr->hasUser($pn)) {
            $sender->sendMessage($api->m('player-not-exist',$pn));
            return false;
        }
        $changes = [];
        $haschanged = false;
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
                case 'ac':
                case 'addcoin':
                case 'addcoins':
                    if($kg[1]<0){
                        $sender->sendMessage($api->m('param-invalid',$kg[0]));
                        return false;
                    }
                    $res = $usermgr->addCoins($pn,$kg[1]);
                    switch($res){
                        case $usermgr::OK:
                            $sender->sendMessage($api->m('success-add-coins',$kg[1]));
                            $haschanged = true;
                            break;
                        case $usermgr::ACCOUNT_FREEZED:
                            $sender->sendMessage($api->m('failed-account-freezed',$pn));
                            break;
                        case $usermgr::OUT_OF_WARNING_LIMIT:
                            $sender->sendMessage($api->m('failed-out-of-warning-limit',$pn));
                            break;
                        case $usermgr::OUT_OF_LIMIT:
                            $sender->sendMessage($api->m('failed-out-of-limit',$pn));
                            break;
                        case $usermgr::ACTION_CANCELED:
                            $sender->sendMessage($api->m('failed-task-canceled'));
                            break;
                        default:
                            $sender->sendMessage($api->m('failed-default'));
                    }
                    break;
                case 'tc':
                case 'takecoin':
                case 'takecoins':
                    if($kg[1]<0){
                        $sender->sendMessage($api->m('param-invalid',$kg[0]));
                        return false;
                    }
                    $res = $usermgr->reduceCoins($pn,$kg[1]);
                    switch($res){
                        case $usermgr::OK:
                            $sender->sendMessage($api->m('success-reduce-coins',$kg[1]));
                            $haschanged = true;
                            break;
                        case $usermgr::ACCOUNT_FREEZED:
                            $sender->sendMessage($api->m('failed-account-freezed',$pn));
                            break;
                        case $usermgr::OUT_OF_WARNING_LIMIT:
                            $sender->sendMessage($api->m('failed-out-of-warning-limit',$pn));
                            break;
                        case $usermgr::OUT_OF_LIMIT:
                            $sender->sendMessage($api->m('failed-out-of-limit',$pn));
                            break;
                        case $usermgr::ACTION_CANCELED:
                            $sender->sendMessage($api->m('failed-task-canceled'));
                            break;
                        default:
                            $sender->sendMessage($api->m('failed-default'));
                    }
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
//                    if($kg[1]<0){
//                        $sender->sendMessage($api->m('param-invalid',$kg[0]));
//                        return false;
//                    }
                    $changes['expire'] = $kg[1]<0?time()+($kg[1]*60*60*24):($kg[1]==0?0:strtotime("+ {$kg[1]} day"));
                    break;
                case 'ad':
                case 'addday':
                case 'addvipday':
                case 'adddays':
                case 'addvipdays':
                    if($kg[1]<0){
                        $sender->sendMessage($api->m('param-invalid',$kg[0]));
                        return false;
                    }
                    $orgd = $usermgr->getUserExpireStamp($pn);
                    if($orgd===-1){
                        $sender->sendMessage($api->m('param-invalid',$kg[0]));
                        return false;
                    }
                    $orgd+= $usermgr->dayToStamp($kg[1]);
                    $changes['expire'] = $orgd;
                    break;
                case 'td':
                case 'takeday':
                case 'takevipday':
                case 'takedays':
                case 'takevipdays':
                    if($kg[1]<0){
                        $sender->sendMessage($api->m('param-invalid',$kg[0]));
                        return false;
                    }
                    $orgd = $usermgr->getUserExpireStamp($pn);
                    if($orgd===-1){
                        $sender->sendMessage($api->m('param-invalid',$kg[0]));
                        return false;
                    }
                    $orgd-= $api->dayToStamp($kg[1]);
                    $changes['expire'] = $orgd;
                    break;
                default:
                    $sender->sendMessage($api->m('param-invalid',$kg[0]));
                    return false;
            }
        }
        if ($changes===[]) {
            if(!$haschanged){
                $sender->sendMessage($api->m('param-invalid',''));
                return false;
            }
            return true;
        }

        if($usermgr->setUser($pn,$changes)===$usermgr::OK) {
            $sender->sendMessage($api->m('cmd-success'));
            return true;
        }

        $sender->sendMessage($api->m('cmd-failed'));
        return false;
    }
}