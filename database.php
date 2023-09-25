<?php
/**
 * Some trivial and very basic ORM implementation.
 */

// Base database class. It's a singleton, so it's initialized only once.
class DB
{
    public static PDO $DB;

    public static function init(string $db_host, string $db_user, string $db_pass, string $db_base): void
    {
        if ($db_host === '') {
            $db_host = 'localhost';
        }
        self::$DB = new PDO("mysql:host=$db_host;dbname=$db_base", $db_user, $db_pass);
        self::$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function query(string $query, array $params = []): PDOStatement
    {
        $stmt = self::$DB->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public static function prepare(string $query): PDOStatement
    {
        return self::$DB->prepare($query);
    }
}

// Base model class. It's a very basic implementation, but it's enough for our purposes.
class Model
{
    /** @var int|null Primary key. Always an ID. */
    public ?int $id = null;

    // Generates table name (pluralized class name).
    public static function tableName(): string
    {
        return strtolower(static::class) . 's';
    }

    // Returns all records from the table matching given conditions (can be ommited).
    public static function all(array $where = []): array
    {
        $sql = "SELECT * FROM " . self::tableName();
        if (count($where)) {
            $sql .= " WHERE " . implode(" AND ", array_map(fn($k) => "$k = ?", array_keys($where)));
        }
        $stmt = DB::prepare($sql);
        $stmt->execute(array_values($where));
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }

    // Returns a record by its primary key (id tbh)
    public static function find(int $id): ?static
    {
        $stmt = DB::query("SELECT * FROM " . self::tableName() . " WHERE id = $id");
        $stmt->setFetchMode(PDO::FETCH_CLASS, static::class);
        return $stmt->fetch();
    }

    // Saves the record to the database. If it has an ID, it's updated, otherwise it's inserted.
    public function save(): void
    {
        $fields = [];
        foreach (get_object_vars($this) as $k => $v) {
            if ($v === null || $k === 'id') {
                continue;
            }
            $fields[$k] = $v;
        }
        $set_str = ' SET ' . implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($fields)));
        if ($this->id !== null) {
            $sql = "UPDATE " . self::tableName() . $set_str . " WHERE id = " . $this->id;
        } else {
            $sql = "INSERT INTO " . self::tableName() . $set_str;
        }
        $stmt = DB::prepare($sql);
        $stmt->execute(array_values($fields));
        if (!$this->id) {
            $this->id = DB::$DB->lastInsertId();
        }
    }

    // Deletes the record from the database.
    public function delete(): void
    {
        DB::query("DELETE FROM " . self::tableName() . " WHERE id = " . $this->id);
    }

}

// Domain model
class Domain extends Model
{
    public string $name;
    public ?string $master;
    public ?int $last_check;
    public string $type;
    public ?int $notified_serial;
    public ?string $account;
    public ?string $options;
    public ?string $catalog;

    // Returns all records for this domain.
    public function records(): array
    {
        return Record::all(['domain_id' => $this->id]);
    }

    // Returns SOA record for this domain.
    public function soa(): Record
    {
        return Record::all(['domain_id' => $this->id, 'type' => 'SOA'])[0];
    }
}

// Domain record model
class Record extends Model
{
    public ?int $domain_id;
    public ?string $name;
    public ?string $type;
    public ?string $content;
    public ?int $ttl;
    public ?int $prio;
    public ?int $change_date;
    public ?int $auth;
    public ?string $ordername;
    public ?int $disabled;
}