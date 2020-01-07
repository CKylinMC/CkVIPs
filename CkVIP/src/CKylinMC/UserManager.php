<?php

declare(strict_types=1);

namespace CKylinMC;

use SQLite3;

class UserManager {
    private $sql,$plugin;

    public const OK = 0;
    public const USER_NOT_EXISTED = 1;
    public const NO_CHANGES = 2;
    public const VIP_LEVEL_INVALID = 3;
    public const PARAM_INVALID = 4;
    public const ACTION_ADD = 1;
    public const ACTION_REDUCE = 2;
    public const ACTION_SET = 3;

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
        $res = $this->sql->get("username='{$name}'");
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
        $res = $this->sql->get("username='{$name}'");
        return count($res)!==0;
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
     * Change player's information.
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
        $this->sql->update($userinfo,"username='{$name}'");
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
        $userinfo = $this->sql->get("username='{$name}'");
        if(empty($userinfo)) {
            return $fallback;
        }
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
        $userinfo = $this->sql->get("username='{$name}'");
        if(empty($userinfo)) {
            return self::USER_NOT_EXISTED;
        }
        $alllvs = $this->plugin->getVIPAvaiableLevels();
        if(!in_array($lv, $alllvs, true)) return self::VIP_LEVEL_INVALID;
        $this->sql->update(['viplevel'=>$lv],"username='$name'");
        return self::OK;
    }

    /**
     * Set when will the player's VIP expired.
     * @param string $name Player's name.
     * @param string $datestr Datetime string, will be convert to timestamp by using function 'strtotime'.
     * @return int Status code.
     */
    public function setUserVIPExpire(string $name, string $datestr):int {
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("username='{$name}'");
        if(empty($userinfo)) {
            return self::USER_NOT_EXISTED;
        }
        $time = strtotime($datestr);
        if($time===false) {
            return self::PARAM_INVALID;
        }
        $this->sql->update(['expire'=>$time],"username='$name'");
        return self::OK;
    }

    /**
     * Check if the player's vip level bigger than 0.
     * @param string $name Player's name
     * @return bool Player is/isn't VIP.
     */
    public function isUserVIP(string $name):bool{
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("username='{$name}'");
        if(empty($userinfo)) {
            return false;
        }
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
        $userinfo = $this->sql->get("username='{$name}'");
        if(empty($userinfo)) {
            return false;
        }
        $now = time();
        $vipexpire = $userinfo['expire'];
        return $now>$vipexpire;
    }

    /**
     * Set Player's coins count.
     * @param string $name Player's name.
     * @param int $action Action code: [ACTION_ADD, ACTION_REDUCE, ACTION_SET].
     * @param int $count Coins count.
     * @param bool $safe Safe mode: coins count can NOT smaller then 0.
     * @return int Status code.
     */
    public function setUserCoins(string $name, int $action, int $count, bool $safe):int{
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("username='{$name}'");
        if(empty($userinfo)) {
            return self::USER_NOT_EXISTED;
        }
        switch ($action){
            case self::ACTION_ADD:
                if($safe && $count <= 0) {
                    return self::PARAM_INVALID;
                }
                $newcoins = $userinfo['coins']+$count;
                $this->sql->update(['coins'=>$newcoins],"username='{$name}'");
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
                $this->sql->update(['coins'=>$newcoins],"username='{$name}'");
                return self::OK;
                break;
            case self::ACTION_SET:
                if($safe && $count < 0) {
                    return self::PARAM_INVALID;
                }
                $this->sql->update(['coins'=>$count],"username='{$name}'");
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
        $userinfo = $this->sql->get("username='{$name}'");
        if(empty($userinfo)) {
            return 0;
        }
        return $userinfo['coins'];
    }

    /**
     * Set Player's account status.
     * @param string $name Player's name.
     * @param string $status Status.
     * @return int Status code.
     */
    public function setUserStatus(string $name, string $status):int{
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("username='{$name}'");
        if(empty($userinfo)) {
            return self::USER_NOT_EXISTED;
        }
        $this->sql->update(['status'=>$status],"username='$name'");
        return self::OK;
    }

    /**
     * Get Player's account status
     * @param string $name Player's name.
     * @return string Status.
     */
    public function getUserStatus(string $name):string{
        $name = $this->sql->safetyInput($name);
        $userinfo = $this->sql->get("username='{$name}'");
        if(empty($userinfo)) {
            return 'PLAYER_NOT_EXIST';
        }
        return $userinfo['status'];
    }

}