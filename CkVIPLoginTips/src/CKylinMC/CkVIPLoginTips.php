<?php

declare(strict_types=1);

namespace CKylinMC;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use CKylinMC\CkVIP;
use CKylinMC\UserManager;

class CkVIPLoginTips extends PluginBase implements Listener {
    /**
     * @var Config
     */
    private $cfg;
    /**
     * @var string
     */
    private $path;
    private $ckapi;
    private $usermgr;

    public function onEnable():void
    {
        $ckvip = $this->getServer()->getPluginManager()->getPlugin("CkVIPCore");
        if($ckvip===null){
            $this->getLogger()->error("This plugin required CkVIPCore plugin!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
//        if(!$this->getServer()->getPluginManager()->isPluginEnabled($ckvip)){
//            $this->getLogger()->error("This plugin required CkVIPCore plugin!");
//            $this->getServer()->getPluginManager()->disablePlugin($this);
//            return;
//        }
        $this->path = $this->getDataFolder();
        @mkdir($this->path);
        $this->cfg = new Config($this->path. 'options.yml', Config::YAML,array(
            'viplevels'=>[
                0//NO VIP
                =>[
                    'enabled' => false,
                    'tip'=>'欢迎 %v %p 加入游戏！'
                ],
                1//VIP 1
                =>[
                    'enabled' => true,
                    'tip'=>'欢迎 %v %p 加入游戏！'
                ],
                2//VIP 2
                =>[
                    'enabled' => true,
                    'tip'=>'欢迎 %v %p 加入游戏！'
                ],
                3//VIP 3
                =>[
                    'enabled' => true,
                    'tip'=>'欢迎 %v %p 加入游戏！'
                ]
            ],
            'vipTip'=>[
                'enabled'=>true,
                'novip_tip'=>'会员畅享多项特权，还不快去购买会员！',
                'remaining_tip'=>'你的 %v 剩余 %d 天。',
                'near_expired_tip'=>'你的 %v 即将过期！',
                'expired_tip'=>'你的会员已过期。'
            ]
        ));
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->ckapi = CkVIP::$API;
        $this->usermgr = $ckvip->getUserMgr();
        $this->getLogger()->info("CkVIPLoginTips Enabled.");
    }
    public function onDisable():void
    {
        $this->getLogger()->info("CkVIPLoginTips Disabled.");
    }

    public function onPlayerJoin(PlayerJoinEvent $e):void{
        $p = $e->getPlayer();
        $pn = $p->getName();
        $lv = $this->usermgr->getUserAvailableVIPLevel($pn);
        echo $lv;
        if($lv===-1){
            $this->getLogger()->error("Errored while getting player's VIP level.");
            $e->setJoinMessage("");
            return;
        }
        if(!($this->cfg->get("viplevels"))[$lv]['enabled']){
            $e->setJoinMessage("");
        }else{
            $alllv = $this->ckapi->getVIPAvaiableLevels();
            $msg = ($this->cfg->get("viplevels"))[$lv]['tip'];
            $msg = str_replace(["%v","%p"],[$alllv[$lv],$pn],$msg);
            $e->setJoinMessage($msg);
        }

        if(!($this->cfg->get("vipTip"))['enabled']){
            return;
        }
        $stamp = $this->usermgr->getUserExpireStamp($pn);
        if($stamp<=0){
            return;
        }
        if($lv===0){
            $p->sendMessage(($this->cfg->get("vipTip"))['novip_tip']);
            return;
        }
        $d = $this->getDay($stamp);
        if($d===-1){
            $p->sendMessage(str_replace('%v',$alllv[$lv],($this->cfg->get("vipTip"))['expired_tip']));
            return;
        }
        if($d===0){
            $p->sendMessage(str_replace('%v',$alllv[$lv],($this->cfg->get("vipTip"))['near_expired_tip']));
            return;
        }
        $p->sendMessage(str_replace(['%v','%d'],[$alllv[$lv],$d],($this->cfg->get("vipTip"))['remaining_tip']));
//        return;
    }

    public function getDay(int $stamp):int{
        $now = time();
        if($now<=$stamp){
            $remaining = $stamp - $now;
            return (int)floor($remaining / 60 / 60 / 24);
        }

        return -1;
    }
}
