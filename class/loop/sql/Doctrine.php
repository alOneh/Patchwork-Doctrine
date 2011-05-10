<?php

class loop_sql_Doctrine extends loop
{
    protected

    $db = false,
    $sql,
    $result,
    $from,
    $count;


    function __construct($sql, $filter = '', $from = null, $count = null)
    {
        $this->sql = $sql;
        $this->from = $from;
        $this->count = $count;
        $this->addFilter($filter);
    }

    function setLimit($from, $count)
    {
        $this->from = $from;
        $this->count = $count;
    }

    protected function prepare()
    {
        $sql = $this->sql;
        $this->db || $this->db = DB();

        if (!is_null($this->count))
        {
            $this->db->modifyLimitQuery($sql, $this->count, $this->from);
        }

        $this->result = $this->db->query($sql);

        return $this->result->rowCount();
    }

    protected function next()
    {
        $a = $this->result->fetch(PDO::FETCH_OBJ);

        if ($a) return $a;
        else $this->result->closeCursor();
    }
}
