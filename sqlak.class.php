class sqlak
{
    var mysqli|false $db;

    function unique($len = 6): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $id = '';
        for ($i = 0; $i < $len; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $id .= $characters[$index];
        }
        return $id;
    }

    private function where($where): string
    {
        if (!$where) {
            $where = "";
        } else {
            $where = " WHERE " . $where;
        }
        return $where;
    }

    function sqlize($value): string
    {
        if (is_string($value)) {
            $value = mysqli_real_escape_string($this->db, $value);
        } else {
            $value = "$value";
        }
        return $value;
    }

    function __construct($config, $db, $options = false)
    {
        $host = $config["host"];
        if ($host == "localhost" && !isset($config["user"]) && !isset($config["pass"])) {
            $user = "root";
            $pass = "";
        } else {
            $user = $config["user"];
            $pass = $config["pass"];
        }
        if (!$options) {
            if (isset($options["port"])) {
                $port = $options["port"];
            } else {
                $port = 3306;
            }
            if (isset($options["socket"])) {
                return $this->db = mysqli_connect($host, $user, $pass, $db, $port, $options["socket"]);
            } else {
                return $this->db = mysqli_connect($host, $user, $pass, $db, $port);
            }
        } else {
            return $this->db = mysqli_connect($host, $user, $pass, $db);
        }
    }

    function db(): bool|mysqli
    {
        return $this->db;
    }


    function do($query): mysqli_result|bool
    {
        return mysqli_query($this->db, $query);
    }

    function set($table, $command, $where = false): bool|mysqli_result
    {
        $where = $this->where($where);
        $commands = "";
        for ($i = 0; $i < count($command); $i++) {
            $commands .= " " . array_keys($command)[$i] . " = '" . $this->sqlize($command[$i]) . "'";
        }
        return $this->do("UPDATE $table SET$commands$where");
    }

    function sel($table, $col = false, $where = false): mysqli_result|bool
    {
        $where = $this->where($where);
        if (!$col) {
            $col = "*";
        }
        return $this->do("SELECT $col FROM $table$where");
    }

    function fessoc($sel)
    {
        return mysqli_fetch_assoc($sel);
    }

    function sqlcount($sel)
    {
        return mysqli_num_rows($sel);
    }

    function give($table, $where = false, $col = false): array
    {
        $res = $this->sel($table, $col, $where);
        $t = [];
        while ($r = $this->fessoc($res)) {
            array_push($t, $r);
        }
        return $t;
    }

    function del($table, $where = false): mysqli_result|bool
    {
        $where = $this->where($where);
        return $this->do("DELETE FROM $table$where");
    }

    function put($table, $command): mysqli_result|bool
    {
        $col = "";
        $val = "";
        for ($i = 0; $i < count($command); $i++) {
            if ($i !== 0) {
                $col .= ",";
                $val .= ",";
            }
            $col .= array_keys($command)[$i];
            $val .= "'" . $this->sqlize($command[array_keys($command)[$i]]) . "'";
        }
        return $this->do("INSERT INTO $table (" . $col . ") VALUES (" . $val . ")");
    }

    function count($table, $where = false): int|string
    {
        return $this->sqlcount($this->sel($table, '*', $where));
    }
}
