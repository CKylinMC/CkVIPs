<?php

declare(strict_types=1);

namespace CKylinMC;

use CKylinMC\Commands\GetPlayerCommand;
use CKylinMC\Commands\MyInfoCommand;
use CKylinMC\Commands\SetPlayerCommand;
use CKylinMC\Listeners\PlayerJoinListener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
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

    public static $API;

    public function onLoad():void
    {
        self::$API = $this;
    }

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
        $this->getServer()->getPluginManager()->registerEvents(new PlayerJoinListener($this), $this);
        $this->path = $this->getDataFolder();
        @mkdir($this->path);
        $this->messages = (new Config(($this->path).'messages.yml',Config::YAML))->getAll();
        $this->getLogger()->info('Loaded language profile: ' .$this->m('lang_profile','','Invalid profile') . ' by @' . $this->m('lang_author','','Unknown') .$this->m('lang_description','',''));
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
            ],
            'default'=>[
                'viplevel'=>0,
                'coins'=>100,
                'expire'=>0,
                'status'=>0
        ]
        ));
        $this->db = new \SQLite3($this->getDataFolder(). 'data.sqlite');
        $this->usermgr = new UserManager($this->db,$this);

        $mgr = PermissionManager::getInstance();
        $mgr->addPermission(new Permission('ckvipcore.cmd.getpl','Allow admins to run getpl command',Permission::DEFAULT_OP));
        $mgr->addPermission(new Permission('ckvipcore.cmd.setpl','Allow admins to run setpl command',Permission::DEFAULT_OP));
        $mgr->addPermission(new Permission('ckvipcore.cmd.myinfo','Allow players to run myinfo command',Permission::DEFAULT_TRUE));

        $m = $this->getServer()->getCommandMap();
        $m->register('ckvipcore',new SetPlayerCommand());
        $m->register('ckvipcore',new GetPlayerCommand());
        $m->register('ckvipcore',new MyInfoCommand());
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
     * @param string $replace Replace String
     * @param string $fallback If message not existed in 'messages.yml' then return
     * @return string|null
     */
    public function m(string $message, string $replace = '', $fallback = null): ?string
    {
        if(array_key_exists($message,$this->messages)) {
            if($replace==='') return $this->messages[$message];
            return str_replace('%s',$replace,$this->messages[$message]);
        }
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
            if(array_key_exists('name',$values)){
                $lvs[$key] = $this->m($values['name']);
            }else continue;
        }
        return $lvs;
    }

    public function getDefaultPlayerConfig():array {
        return $this->cfg->get('default');
    }

    public function getStatusText(int $stat):string{
        switch($stat){
            case 0:
                $txt = 'status-normal';
                break;
            case 1:
                $txt = 'status-freezed';
                break;
            case 2:
                $txt = 'status-warning';
                break;
            default:
                $txt = 'unknow';
        }
        return $this->m($txt);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        return true;
    }
}
