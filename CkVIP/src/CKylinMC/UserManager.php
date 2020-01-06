<?php

declare(strict_types=1);

namespace CKylinMC;

class UserManager {
    private $sql;
    public function __construct(\SQLite3 $db)
    {
        $this->sql = new SQLiteProvider($db);
    }

    //TODO: UserManager Codes

}