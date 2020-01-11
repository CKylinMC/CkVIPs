<?php

declare(strict_types=1);

namespace CKylinMC;

use CKylinMC\Events\AccountStatusChanged;
use CKylinMC\Events\PlayerAddCoinsEvent;
use CKylinMC\Events\PlayerCoinsChangedEvent;
use CKylinMC\Events\PlayerCreateEvent;
use CKylinMC\Events\PlayerReduceCoinsEvent;
use CKylinMC\Events\PlayerSetCoinsEvent;
use CKylinMC\Events\PlayerSetVipExpireDayEvent;
use CKylinMC\Events\PlayerSetVipLevelEvent;
use SQLite3;
use const Grpc\STATUS_CANCELLED;

class UserManager {
    private $sql,$plugin;

    public const OK = 0;
    public const USER_NOT_EXISTED = 1;
    public const NO_CHANGES = 2;
    public const VIP_LEVEL_INVALID = 3;
    public const PARAM_INVALID = 4;
    public const USER_EXISTED = 5;

    public const ACTION_ADD = 1;
    public const ACTION_REDUCE = 2;
    public const ACTION_SET = 3;

    public const ACCOUNT_FREEZED = 6;

    public const ACTION_CANCELED = 7;

    // Users account status
    public const USER_STATUS_NORMAL = 0;
    public const USER_STATUS_FREEZED = 1;
    public const USER_STATUS_WARNING = 2;

    public function __construct(SQLite3 $db, CkVIP $plugin)
    {
        $this->sql = new SQLiteProvider($db);
        $this->plugin = $plugin;
    }

    /**
     * Get player's information.
     * @param string $name Player's name.
     * @return array Player's information.
     */
    public function getUser(string $name):array{
        $name = $this->sql->safetyInput($name);
        $res = $this->sql->get("player='{$name}'");
        if(count($res)) {
            return $res[0];
        }
        return [];
    }

    /**
     * Check if player existed in database.
     * @param string $name Player's name.
     * @return bool
     */
    public function hasUser(string $name):bool {
        $name = $this->sql->safetyInput($name);
        $res = $this->sql->get("player='{$name}'");
        return !empty($res);
    }

    /**
     * Get all supported parameters for player's information.
     * @return array Supported parameters list.
     */
    public function getAllowedParameters():array{
        return [
            'id',
            'player',
            'viplevel',
            'expire',
            'coins',
            'status'
        ];
    }

    /**
     * Change player's information.[Not Recommended]
     * @param string $name Player's name.
     * @param array $mods Changes. Format: array( array('Key'=>'Value')[,array('Key'=>'Value')...] )
     * @return int Status code.
     */
    public function setUser(string $name, array $mods):int{
        $config = $userinfo = $this->getUser($name);
        if(empty($userinfo)) {
            return self::USER_NOT_EXISTED;
        }
        $allowedParameters = $this->getAllowedParameters();
        foreach($mods as $key => $value){
            if(!in_array($key, $allowedParameters, true)) continue;
            $userinfo[$key] = $value;
        }
        if($config===$userinfo){
            return self::NO_CHANGES;
        }
        $this->sql->update($userinfo,"player='{$name}'");
        return self::OK;
    }

    public function createUser(string $name, array $config = []): int{
        if($this->hasUser($name)) {
            return self::USER_EXISTED;
        }
        $allowedcolumns = $this->getAllowedParameters();
        $preconfig = $this->plugin->getDefaultPlayerConfig();
        foreach ($config as $key => $value){
            if(!in_array($key, $allowedcolumns, true)) {
                continue;
            }
            if($key === 'ID') {
                continue;
            }
            $preconfig[$key] = $value;
        }
        $ev = new PlayerCreateEvent($this->plugin,$name,$preconfig);
        $ev->call();
        if($ev->isCancelled()){
            return self::ACTION_CANCELED;
        }
        $preconfig = $ev->getConfig();
        $preconfig['player'] = $name;
        $this->sql->insert($preconfig);
        return self::OK;
    }

    /**
     * Get Player's VIP level.
     * @param string $name Player's name.
     * @param int $fallback If the vip-level saved is invalid, then return.
     * @return int VIP level.
     */
    public function getUserVIPLevel(string $name, int $fallback = 0):int{
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("player='{$name}'");
        if(empty($userinfo)) {
            return $fallback;
        }
        $userinfo = $userinfo[0];
        $alllvs = $this->plugin->getVIPAvaiableLevels();
        if(!in_array($userinfo['viplevel'], $alllvs, true)) return $fallback;
        return $userinfo['viplevel'];

    }

    /**
     * Set Player's VIP level.
     * @param string $name Player's name.
     * @param int $lv VIP level.
     * @return int Status code.
     */
    public function setUserVIPLevel(string $name, int $lv ):int{
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("player='{$name}'");
        if(empty($userinfo)) {
            return self::USER_NOT_EXISTED;
        }
        $userinfo = $userinfo[0];
        $alllvs = $this->plugin->getVIPAvaiableLevels();
        if(!in_array($lv, $alllvs, true)) return self::VIP_LEVEL_INVALID;
        $ev = new PlayerSetVipLevelEvent($this->plugin,$name,$lv);
        $ev->call();
        if($ev->isCancelled()){
            return self::ACTION_CANCELED;
        }
        $this->sql->update(['viplevel'=>$lv],"player='$name'");
        return self::OK;
    }

    /**
     * Set when will the player's VIP expired.
     * @param string $name Player's name.
     * @param int $day VIP days from now.
     * @return int Status code.
     */
    public function setUserVIPExpire(string $name, int $day):int {
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("player='{$name}'");
        if(empty($userinfo)) {
            return self::USER_NOT_EXISTED;
        }
        $userinfo = $userinfo[0];
        $ev = new PlayerSetVipExpireDayEvent($this->plugin,$name,$day);
        $ev->call();
        if($ev->isCancelled()){
            return self::ACTION_CANCELED;
        }
        $time = strtotime('+ '.$day.' day');
        if($time===false) {
            return self::PARAM_INVALID;
        }
        $this->sql->update(['expire'=>$time],"player='$name'");
        return self::OK;
    }

    /**
     * Check if the player's vip level bigger than 0.
     * @param string $name Player's name
     * @return bool Player is/isn't VIP.
     */
    public function isUserVIP(string $name):bool{
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("player='{$name}'");
        if(empty($userinfo)) {
            return false;
        }
        $userinfo = $userinfo[0];
        return $userinfo['viplevel']>0;
    }

    /**
     * Check if the player's vip still available.
     * @param string $name Player's name.
     * @return bool Available/Unavailable.
     */
    public function isUserVIPAvailable(string $name): bool
    {
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("player='{$name}'");
        $userinfo = $userinfo[0];
        if($userinfo===[]) {
            return false;
        }
        if($userinfo['expire']===0) {//0 = forever
            return true;
        }
        $now = time();
        $vipexpire = $userinfo['expire'];
        return $now<$vipexpire;
    }

    public function getUserAvailableVIPLevel(string $name,int $novipLevel = 0,int $fallbackLevel = -1):int{
        if(!$this->hasUser($name)){
            return $fallbackLevel;
        }
        $vip = $this->getUserVIPLevel($name);
        return $this->isUserVIPAvailable($name)?$vip:$novipLevel;
    }

    /**
     * Set Player's coins count.
     * @param string $name Player's name.
     * @param int $action Action code: [ACTION_ADD, ACTION_REDUCE, ACTION_SET].
     * @param int $count Coins count.
     * @param bool $safe Safe mode: coins count can NOT smaller then 0.
     * @return int Status code.
     */
    public function setUserCoins(string $name, int $action, int $count, bool $safe = true):int{
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("player='{$name}'");
        if(empty($userinfo)) {
            return self::USER_NOT_EXISTED;
        }
        $userinfo = $userinfo[0];
        $ev = new PlayerSetCoinsEvent($this->plugin,$name,$count,$action);
        $ev->call();
        if($ev->isCancelled()){
            return self::ACTION_CANCELED;
        }
        switch ($action){
            case self::ACTION_ADD:
                if($safe && $count <= 0) {
                    return self::PARAM_INVALID;
                }
                $newcoins = $userinfo['coins']+$count;
                $this->sql->update(['coins'=>$newcoins],"player='{$name}'");
                return self::OK;
                break;
            case self::ACTION_REDUCE:
                if($safe && $count <= 0) {
                    return self::PARAM_INVALID;
                }
                $newcoins = $userinfo['coins']-$count;
                if($safe && $newcoins < 0) {
                    $newcoins = 0;
                }
                $this->sql->update(['coins'=>$newcoins],"player='{$name}'");
                return self::OK;
                break;
            case self::ACTION_SET:
                if($safe && $count < 0) {
                    return self::PARAM_INVALID;
                }
                $this->sql->update(['coins'=>$count],"player='{$name}'");
                return self::OK;
                break;
            default:
                return self::PARAM_INVALID;
        }
    }

    /**
     * Get Player's coins count.
     * @param string $name Player's name.
     * @return int Coins count.
     */
    public function getUserCoins(string $name):int{
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("player='{$name}'");
        if(empty($userinfo)) {
            return -1;
        }
        $userinfo = $userinfo[0];
        return $userinfo['coins'];
    }

    /**
     * Set Player's account status.
     * @param string $name Player's name.
     * @param int $status Status.
     * @return int Status code.
     */
    public function setUserStatus(string $name, int $status):int{
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("player='{$name}'");
        if(empty($userinfo)) {
            return self::USER_NOT_EXISTED;
        }

        $userinfo = $userinfo[0];
        $ev = new AccountStatusChanged($this->plugin,$name,$status);
        $ev->call();
        if($ev->isCancelled()){
            return self::ACTION_CANCELED;
        }
        $status = $ev->getStatus();
        $this->sql->update(['status'=>$status],"player='$name'");
        return self::OK;
    }

    /**
     * Get Player's account status
     * @param string $name Player's name.
     * @return string Status.
     */
    public function getUserStatus(string $name):string{
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("player='{$name}'");
        if(empty($userinfo)) {
            return 'PLAYER_NOT_EXIST';
        }
        $userinfo = $userinfo[0];
        return $userinfo['status'];
    }

    /**
     * Get Player's account status
     * @param string $name Player's name.
     * @return string Status.
     */
    public function getUserExpire(string $name, bool $msgfallback = true):string{
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("player='{$name}'");
        if(empty($userinfo)) {
            if($msgfallback){
                return $this->plugin->m('player-not-exist',$name);
            }else{
                return '';
            }
        }
        $userinfo = $userinfo[0];
        $e = $userinfo['expire'];
        return $this->dateStr($e);
    }

    public function dateStr(int $stamp):string{
        if($stamp===0){
            return $this->plugin->m('forever');
        }
        return date('Y-m-d H:i:s',$stamp);
    }

    public function addCoins(string $player,int $coins):int{
        if($coins<=0){
            return self::PARAM_INVALID;
        }
        if(!$this->hasUser($player)){
            return  self::USER_NOT_EXISTED;
        }
        $ev = new PlayerAddCoinsEvent($this->plugin,$player,$coins);
        $ev->call();
        if($ev->isCancelled()){
            return self::ACTION_CANCELED;
        }
        $res = $this->setUserCoins($player,self::ACTION_ADD,$ev->getCoins());
        if(!$res === self::OK){
            return $res;
        }
        (new PlayerCoinsChangedEvent($this->plugin,$player))->call();
        return self::OK;
    }

    public function reduceCoins(string $player,int $coins):int{
        if($coins<=0){
            return self::PARAM_INVALID;
        }
        if(!$this->hasUser($player)){
            return  self::USER_NOT_EXISTED;
        }
        $ev = new PlayerReduceCoinsEvent($this->plugin,$player,$coins);
        $ev->call();
        if($ev->isCancelled()){
            return self::ACTION_CANCELED;
        }
        $res = $this->setUserCoins($player,self::ACTION_REDUCE,$ev->getCoins());
        if(!$res === self::OK){
            return $res;
        }
        (new PlayerCoinsChangedEvent($this->plugin,$player))->call();
        return self::OK;
    }

}