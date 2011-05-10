<?php

namespace Doctrine\DBAL;

class Connection extends self
{
    public function delete($tableName, array $identifier)
    {
        list($tableName, $identifier) = $this->quoteArgsAsIdentifiers($tableName, $identifier);

        return parent::delete($tableName, $identifier);
    }

    public function update($tableName, array $data, array $identifier)
    {
        list($tableName, $data, $identifier) = $this->quoteArgsAsIdentifiers($tableName, $data, $identifier);

        return parent::update($tableName, $data, $identifier);
    }

    public function insert($tableName, array $data)
    {
        list($tableName, $data) = $this->quoteArgsAsIdentifiers($tableName, $data);

        return parent::insert($tableName, $data);
    }


    protected function quoteArgsAsIdentifiers()
    {
        $a = func_get_args();
        $c = $this->quoteIdentifier('');

        foreach ($a as &$data)
        {
            if (is_string($data))
            {
                if ('' === $data || $c[0] !== $data[0])
                    $data = $this->quoteIdentifier($data);
            }
            else
            {
                $quotedData = array();
                foreach ($data as $k => $v)
                {
                    list($k) = $this->quoteArgsAsIdentifiers($k);
                    $quotedData[$k] = $v;
                }
                $data = $quotedData;
            }
        }

        return $a;
    }
}