<?php

require_once 'APP/Db/Adapter/Abstract.php';

class APP_Db_Adapter_Mysql extends APP_Db_Adapter_Abstract
{
    protected $_pdoType = 'mysql';

    public function getQuoteIdentifierSymbol()
    {
        return "`";
    }

    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
        return $this->fetchCol('SHOW TABLES');
    }

    /**
     * 返回表的列描述
     *
     * 返回的结果集是以列名为key的相关数组
     *
     * 该相关数组的每一个元素的值具有如下含义;
     *
     * SCHEMA_NAME      => string;  Database's name
     * TABLE_NAME       => string;  table's name
     * COLUMN_NAME      => string;  column's name
     * COLUMN_POSITION  => number;  该列在表中的原始位置(以1起始)
     * DATA_TYPE        => string;  SQL datatype name of column
     * DEFAULT          => string;  该列的默认值default expression of column, null if none
     * NULLABLE         => boolean; 该列是否可以为NULL
     * LENGTH           => number;  char/varchar的长度
     * SCALE            => number;  scale of NUMERIC/DECIMAL
     * PRECISION        => number;  precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName, $schemaName = null)
    {
        if (!$schemaName){
            $schemaName = $this->_config['dbname'];
        }

        $sql = 'DESCRIBE ' . $this->quoteIdentifier("$schemaName.$tableName");
        $stmt = $this->query($sql);

        $result = $stmt->fetchAll(PDO::FETCH_NUM);
        $field   = 0;
        $type   = 1;
        $null   = 2;
        $key     = 3;
        $default = 4;
        $extra   = 5;

        $desc = array();
        $i = 1;
        $p = 1;

        foreach ($result as $row)
        {
            list($length, $scale, $precision, $unsigned, $primary, $primaryPosition, $identity)
                = array(null, null, null, null, false, null, false);
            if (preg_match('/unsigned/', $row[$type]))
            {
                $unsigned = true;
            }

            if (preg_match('/^((?:var)?char)\((\d+)\)/', $row[$type], $matches))
            {
                $row[$type] = $matches[1];
                $length = $matches[2];
            }
            else if (preg_match('/^decimal\((\d+),(\d+)\)/', $row[$type], $matches))
            {
                $row[$type] = 'decimal';
                $precision = $matches[1];
                $scale = $matches[2];
            }
            else if (preg_match('/^((?:big|medium|small|tiny)?int)\((\d+)\)/', $row[$type], $matches))
            {
                $row[$type] = $matches[1];
//              $length = $matches[2];  //just for display width
            }
            if (strtoupper($row[$key]) == 'PRI')
            {
                $primary = true;
                $primaryPosition = $p;
                if ($row[$extra] == 'auto_increment')
                {
                    $identity = true;
                }
                else
                {
                    $identity = false;
                }
                ++$p;
            }
            $desc[$this->foldCase($row[$field])] = array(
                'SCHEMA_NAME'     => $schemaName,
                'TABLE_NAME'       => $this->foldCase($tableName),
                'COLUMN_NAME'     => $this->foldCase($row[$field]),
                'COLUMN_POSITION'  => $i,
                'DATA_TYPE'     => $row[$type],
                'DEFAULT'         => $row[$default],
                'NULLABLE'       => (bool) ($row[$null] == 'YES'),
                'LENGTH'           => $length,
                'SCALE'         => $scale,
                'PRECISION'     => $precision,
                'UNSIGNED'       => $unsigned,
                'PRIMARY'         => $primary,
                'PRIMARY_POSITION' => $primaryPosition,
                'IDENTITY'       => $identity
            );
            ++$i;
        }
        return $desc;
    }

    /**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param  string $sql
     * @param  integer $count
     * @param  integer $offset OPTIONAL
     * @throws APP_Db_Adapter_Exception
     * @return string
     */
     public function limit($sql, $count, $offset = 0)
     {
        $count = intval($count);
        if ($count <= 0)
        {
            require_once 'APP/Db/Exception.php';
            throw new APP_Db_Exception("LIMIT argument count=$count is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0)
        {
            require_once 'APP/Db/Exception.php';
            throw new APP_Db_Exception("LIMIT argument offset=$offset is not valid");
        }

        $sql .= " LIMIT $count";
        if ($offset > 0)
        {
            $sql .= " OFFSET $offset";
        }

        return $sql;
    }
}

