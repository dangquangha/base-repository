<?php

namespace App\Libraries\Traits;

trait ScopeRepositoryTrait
{
    public function scopeFilter($query, $filter = [])
    {
        if ($filter)
        {
            $query = $query->where(function ($q) use ($filter)
            {
                foreach ($filter as $f) {
                    list($col, $ope, $val) = $f;
                    $q->where($col, $ope, $val);
                }
            });
        }

        return $query;
    }

    public function scopeSort($query, $sorts = array())
    {
        if ($sorts)
        {
            foreach ($sorts as $sort) {
                list($col, $dir) = $sort;
                $query->orderBy($col, $dir);
            }
        }
        return $query;
    }
}