<?php

declare(strict_types=1);

namespace CKylinMC;

class SQLiteProvider{
    protected $db;
    private $tablename = 'users';
    public function __construct(\SQLite3 $db)
    {
        $this->db = $db;
        $this->db->exec('CREATE TABLE IF NOT EXISTS '.$this->tablename.' ( id INTEGER PRIMARY KEY AUTOINCREMENT, player VARCHAR NOT NULL, viplevel INT NOT NULL, coins INT NOT NULL, expire TIMESTAMP NOT NULL, status int NOT NULL );');
//        $this->db->exec("UPDATE SQLITE_SEQUENCE SET SEQ = 1 WHERE NAME = '{$this->tablename}'");
    }

    public function getDB():\SQLite3{
        return $this->db;
    }

    public function setWorkingTable(string $table = 'users'):void{
        $this->tablename = $table;
    }

    public function useTable(string $table):void{
        $this->db->exec("use ".$table);
        $this->setWorkingTable($table);
    }

    public function getWorkingTable():string{
        return $this->tablename;
    }

    public function query(string $q):array{
        return $this->sqlResultToArray($this->db->query($q));
    }

    public function get(string $condition = ''):array{
        $where = $condition===''?'':'WHERE '.$condition;
        return $this->query("SELECT * FROM {$this->tablename} {$where}");
    }

    public function insert(array $keyspair):void{
        $columns = '';
        $values = '';
        $total = count($keyspair);
        $counter = 0;
        foreach ($keyspair as $column=>$value){
            $counter++;
            $end = $total!==$counter?',':'';
            $columns.= $column.$end;
            $value = is_string($value)?$this->safetyInput($value):$value;
            $values.= "\"{$value}\"".$end;
        }
        $sql = "INSERT INTO {$this->tablename} ( {$columns} ) VALUES ( {$values} );";
        $this->db->exec($sql);
    }

    public function update(array $keyspair, string $check = ''):void {
        $where = $check===''?'':'WHERE '.$check;
        $keys = '';
        $total = count($keyspair);
        $counter = 0;
        foreach ($keyspair as $column=>$value){
            $counter++;
            $end = $total!==$counter?',':'';
            $value = is_string($value)?$this->safetyInput($value):$value;
            $keys.= "$column=\"{$value}\"".$end;
        }
        $sql = "UPDATE {$this->tablename} SET {$keys} {$where}";
        $this->db->exec($sql);
    }

    public function set(array $keyspair, string $check):void{
        if(count($this->get($check))>0){
            $this->update($keyspair,$check);
        }
        $this->insert($keyspair);
    }

    public function del(string $check):void{
        if(count($this->get($check))>0){
            $this->db->exec("DELETE FROM {$this->tablename} WHERE {$check}");
        }
    }
    
    public function sqlResultToArray(\SQLite3Result $res):array{
        $arr = [];
        while($row = $res->fetchArray(SQLITE3_ASSOC)){
            $arr[] = $row;
        }
        return $arr;
    }
    public function safetyInput(string $str):string {
        // $str = mysql_real_escape_string($str);
        $str = addslashes($str);
//        $str = htmlentities($str);
        return $str;
    }
}
