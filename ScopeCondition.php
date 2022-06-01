<?php

namespace App\Libraries\Traits;

trait ScopeCondition
{
    public function scopeRelation($result, $relation)
    {
        foreach ($relation as $item)
        {
            $name      = array_get($item, 'name');
            $field     = array_get($item, 'field');
            $field     = is_string($field) ? explode(',', $field) : $field;
            $condition = array_get($item, 'condition');
            $limit     = array_get($item, 'limit');
            $order     = array_get($item, 'order');

            $result = $result->with([
                $name => function ($query) use ($field, $condition, $limit, $order)
                {
                    if ($condition)
                    {
                        $query = $query->where(function ($q) use ($condition)
                        {
                            foreach ($condition as $item)
                            {
                                list($col, $ope, $val) = $item;
                                $q->where($col, $ope, $val);
                            }
                        });
                    }
                    if ($field) $query->select($field);

                    if ($order)
                    {
                        $order = is_string($field) ? explode(',', $order) : $order;
                        list($column, $direction) = $order;
                        $query->orderBy($column, $direction);
                    }
                    if ($limit)
                    {
                        $query->limit($limit);
                    }
                }
            ]);
        }

        return $result;
    }

    public function scopeWhereIn($result, $whereIn)
    {
        if (count($whereIn) != 2)
        {
            return $result;
        }
        $col = $whereIn[0];
        $val = $whereIn[1];

        return $result->whereIn($col, (array)$val);
    }

    public function scopeWhereNotIn($result, $whereNotIn)
    {
        if (count($whereNotIn) != 2)
        {
            return $result;
        }
        $col = $whereNotIn[0];
        $val = $whereNotIn[1];

        return $result->whereNotIn($col, (array)$val);
    }

    public function scopeWhere($result, $where)
    {
        $result = $result->where(function ($q) use ($where)
        {
            foreach ($where as $item)
            {
                list($col, $ope, $val) = $item;
                if ($val !== null)
                {
                    $q->where($col, $ope, $val);
                }
            }
        });

        return $result;
    }

    public function scopeOrWhere($result, $orWhere)
    {
        $result = $result->where(function ($q) use ($orWhere)
        {
            foreach ($orWhere as $item)
            {
                list($col, $ope, $val) = $item;
                if ($val !== null)
                {
                    $q->orWhere($col, $ope, $val);
                }
            }
        });

        return $result;
    }

    public function scopeOrder($result, $order)
    {
        $order = is_string($order) ? explode(',', $order) : $order;
        list($col, $dir) = $order;
        $result = $result->orderBy($col, $dir);

        return $result;
    }

    public function scopeBetween($result, $between)
    {
        $between = is_string($between) ? explode(',', $between) : $between;
        list($column, $start, $end) = $between;

        return $result->whereBetween($column, [$start, $end]);
    }

    public function scopeJoin($result, $joins)
    {
        foreach ($joins as $item)
        {
            list($table, $table_col, $ope, $source_col) = $item;
            $result = $result->join($table, $table_col, $ope, $source_col);
        }

        return $result;
    }

    public function scopeWhereDate($result, $whereDate)
    {
        list($col, $ope, $val) = $whereDate;
        if ($val !== null)
        {
            $result = $result->whereDate($col, $ope, $val);
        }

        return $result;
    }

    public function scopeWhereMonth($result, $whereMonth)
    {
        list($col, $ope, $val) = $whereMonth;
        if ($val !== null)
        {
            $result = $result->whereMonth($col, $ope, $val);
        }

        return $result;
    }
}

