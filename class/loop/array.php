<?php

class loop_array extends self
{
    function __construct($array, $filter = '', $isAssociative = null)
    {
        parent::__construct($array, $filter, $isAssociative);

        if ('filter_rawArray' === $filter) $this->addFilter(array($this, 'filterDateTime'));
    }

    function filterDateTime($data)
    {
        foreach ($data as $k => &$v)
        {
            if ($v instanceof DateTime)
            {
                $k .= '_timestamp';
                $data->$k = $v->format('U');
                $v = $v->format('c');
            }
        }

        return $data;
    }

}