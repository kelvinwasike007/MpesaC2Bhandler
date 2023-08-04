<?php
class DB
{
    protected $query;
    protected $pdo;
    protected $parameters = [];
    protected $error;

    public function __construct($dbname, $user = null, $password = null)
    {
        $dsn = "sqlite:$dbname";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];

        if ($user !== null && $password !== null) {
            $dsn .= ";user=$user;password=$password";
        }

        try {
            $this->pdo = new PDO($dsn, $user, $password, $options);
        } catch (PDOException $e) {
            $this->error = "Connection failed: " . $e->getMessage();
        }
    }

    public function select($columns = '*')
    {
        $this->query = "SELECT " . $this->parseColumns($columns);
        return $this;
    }

    public function from($table)
    {
        $this->query .= " FROM $table";
        return $this;
    }

    public function where($column, $operator, $value)
    {
        $this->query .= " WHERE $column $operator ?";
        $this->parameters[] = $value;
        return $this;
    }

    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $this->query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->parameters = array_values($data);
        return $this;
    }

    public function update($table, $data)
    {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "$column = ?";
            $this->parameters[] = $value;
        }

        $this->query = "UPDATE $table SET " . implode(', ', $set);
        return $this;
    }

    public function delete($table)
    {
        $this->query = "DELETE FROM $table";
        return $this;
    }

    public function createTable($tableName, $columns)
    {
        $this->query = "CREATE TABLE IF NOT EXISTS $tableName (";

        $columnDefinitions = [];
        foreach ($columns as $columnName => $columnType) {
            $columnDefinitions[] = "$columnName $columnType";
        }

        $this->query .= implode(', ', $columnDefinitions);
        $this->query .= ")";
        return $this;
    }

    public function dropTable($tableName)
    {
        $this->query = "DROP TABLE IF EXISTS $tableName";
        return $this;
    }

    public function renameTable($oldTableName, $newTableName)
    {
        $this->query = "ALTER TABLE $oldTableName RENAME TO $newTableName";
        return $this;
    }

    public function addColumn($tableName, $columnName, $columnType)
    {
        $this->query = "ALTER TABLE $tableName ADD COLUMN $columnName $columnType";
        return $this;
    }

    public function dropColumn($tableName, $columnName)
    {
        $this->query = "ALTER TABLE $tableName DROP COLUMN $columnName";
        return $this;
    }

    public function modifyColumn($tableName, $columnName, $columnType)
    {
        $this->query = "ALTER TABLE $tableName MODIFY COLUMN $columnName $columnType";
        return $this;
    }

    public function migrate()
    {
        return $this->tryCatch(function () {
            $this->pdo->beginTransaction();
            $this->pdo->exec($this->query);
            $this->pdo->commit();
            return true;
        });
    }

    public function execute()
    {
        return $this->tryCatch(function () {
            $stmt = $this->pdo->prepare($this->query);
            $stmt->execute($this->parameters);
            return $stmt;
        });
    }

    public function tryCatch(callable $callback)
    {
        try {
            return $callback();
        } catch (PDOException $e) {
            $this->error = "Error: " . $e->getMessage();
        }
    }

    protected function parseColumns($columns)
    {
        if (is_array($columns)) {
            return implode(', ', $columns);
        } elseif ($columns === '*') {
            return '*';
        } else {
            return $columns;
        }
    }

    public function getError()
    {
        return $this->error;
    }
    
    public function listTables()
    {
        $query = "SELECT name FROM sqlite_master WHERE type='table'";

        $result = $this->tryCatch(function () use ($query) {
            return $this->pdo->query($query);
        });

        if ($result) {
            $tables = $result->fetchAll(PDO::FETCH_COLUMN);

            return $tables;
        }

        return [];
    }
}

// // Example usage with database configuration
// $dbConfig = [
//     'dbname' => 'your_database.sqlite',
//     'user' => 'your_username',
//     'password' => 'your_password',
// ];

// $queryBuilder = new SQLiteQueryBuilder(
//     $dbConfig['dbname'],
//     $dbConfig['user'],
//     $dbConfig['password']
// );

// // Check for any connection errors
// if ($queryBuilder->getError()) {
//     die($queryBuilder->getError());
// }

// // Example migration: Create a new table
// $queryBuilder->createTable('users', [
//     'id' => 'INTEGER PRIMARY KEY',
//     'name' => 'TEXT',
//     'age' => 'INTEGER'
// ])->migrate();

// // Example usage: SELECT query
// $selectQuery = $queryBuilder->select(['id', 'name'])
//     ->from('users')
//     ->where('age', '>', 18)
//     ->getQuery();
// // $selectQuery will contain: "SELECT id, name FROM users WHERE age > ?"

// // ... (Other examples of SELECT, INSERT, UPDATE, DELETE queries)
