<?php

declare(strict_types=1);

namespace CKylinMC;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class CkVIP extends PluginBase{
    private $path;
    public
        $messages,
        $usermgr,
        $cfg;
    /**
     * @var \SQLite3
     */
    private $db;

    public function onEnable():void
    {
        $this->init();
        $this->getLogger()->info('CkVIP Enabled.');
    }
    public function onDisable():void
    {
        $this->getLogger()->info('CkVIP Disabled.');
    }
    public function init():void{
        $this->saveResource('messages.yml');
        $this->path = $this->getDataFolder();
        @mkdir($this->path);
        $this->messages = (new Config(($this->path).'messages.yml',Config::YAML))->getAll();
        $this->getLogger()->info('Loaded language profile: ' .$this->m('lang_profile','Invalid profile'));
        $this->cfg = new Config($this->path. 'options.yml', Config::YAML,array(
            'coin'=>'GP',
            'viplevels'=>[
                0//NO VIP
                =>[
                    'name'=>'vip_0'
                ],
                1//VIP 1
                =>[
                    'name'=>'vip_1'
                ],
                2//VIP 2
                =>[
                    'name'=>'vip_2'
                ],
                3//VIP 3
                =>[
                    'name'=>'vip_3'
                ],
            ]
        ));
        $this->db = new \SQLite3($this->getDataFolder(). 'data.sqlite');
        $this->usermgr = new UserManager($this->db);
    }

    /**
     * Get CkVIP users manager instance.
     * @return UserManager
     */
    public function getUserMgr():UserManager{
        return $this->usermgr;
    }

    /**
     * Return translated messages.
     * @param string $message Message name
     * @param string $fallback If message not existed in 'messages.yml' then return
     * @return string|null
     */
    public function m(string $message, $fallback = null): ?string
    {
        if(array_key_exists($message,$this->messages)) return $this->messages[$message];
        if($fallback!==null) return $fallback;
        return $message;
    }

    /**
     * Get all avaiable VIP levels.
     * @return array
     */
    public function getVIPAvaiableLevels():array{
        $lvs = [];
        foreach($this->cfg->get('viplevels') as $key=>$values){
            if(in_array('name',$values)){
                $lvs[$key] = $this->m($values['name']);
            }else continue;
        }
        return $lvs;
    }
}
