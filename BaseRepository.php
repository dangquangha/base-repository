<?php

namespace App\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Traits\ScopeCondition;
use App\Traits\ScopeRepositoryTrait;

abstract class BaseRepository
{
    use ScopeRepositoryTrait;
    use ScopeCondition;

    protected $total = 0;
    protected $model;

    /**
     * @param bool $filter
     * @param bool $sort
     * @param bool $limit
     * @param array $select
     * @return mixed
     */
    public function getAll($filter = false, $sort = false, $limit = false, $select = [])
    {
        $query = $this->model;
        $query = $this->scopeFilter($query, $filter);
        if ($select) $query->select($select);

        if ($sort)
        {
            list($col, $dir) = $sort;
            $query = $query->orderBy($col, $dir);
        }

        return $limit ? $query->paginate($limit) : $query->get($select);
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * @param $id
     * @param array $select
     * @return mixed
     */
    public function findById($id, $select = ['*'])
    {
        return $this->model->findOrFail($id, $select);
    }

    /**
     * @param array $condition
     * @param array $field
     * @return mixed
     */
    public function findBy($condition = array(), $field = array('*'))
    {
        $filter = Arr::get($condition, 'filter');
        if (!$filter) return null;

        $query = $this->model;
        $item = $this->scopeFilter($query, $filter)->first($field);
        return $item;
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function updateOrCreateData($id, $data = array())
    {
        return $this->model->updateOrCreate([$this->model->getPrimaryKey() => $id], $data);
    }

    /**
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateById($id, $data)
    {
        $model = $this->findById($id);
        $model->fill($data)->save();
        return $model;
    }

    /**
     * @param $id
     * @param $field
     * @param string $otherValue
     * @return mixed
     */
    public function updateByField($id, $field, $otherValue = '')
    {
        $row = $this->findById($id);
        $row->$field = ($otherValue ? $otherValue : (($row->$field == 1) ? 0 : 1));
        $row->save();
        return $row;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return is_array($id) ? $this->model->destroy($id) : $this->findById($id)->delete();
    }

    /**
     * @param $value
     * @param $key
     * @return mixed
     */
    public function getPluck($value, $key)
    {
        return $this->model->pluck($value, $key);
    }

    /**
     * @return mixed
     */
    public function getInstance()
    {
        return new $this->model;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function insert($data=[])
    {
        return DB::table($this->model->getTable())->insert($data);
    }

    /**
     * @param array $data
     * @return int
     */
    public function insertGetId($data=[])
    {
        return DB::table($this->model->getTable())->insertGetId($data);
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data)
    {
        return $this->model->where('id', $id)->update($data);
    }

    /**
     * @param $column
     * @param $value
     * @param array $data
     * @return mixed
     */
    public function updateBy($column, $value, array $data)
    {
        return $this->model->where($column, $value)->update($data);
    }

    /**
     * @param array $filter
     * @return mixed
     */
    public function countBy(array $filter)
    {
        $query = $this->model->whereRaw(1);
        $query = $this->scopeFilter($query, $filter);

        return $query->count();
    }

    /**
     * @param $id
     * @param $column
     * @param int $hit
     * @return mixed
     */
    public function increment($id, $column, $hit=1)
    {
        return $this->model->where('id', $id)->increment($column, $hit);
    }

    /**
     * Note:
     * @param $filter
     * @param $data
     * @return mixed
     */
    public function updateOrCreate($filter, $data)
    {
        return $this->model->updateOrCreate($filter, $data);
    }

    /**
     * Note:
     * @param $id
     * @param $column
     * @return mixed
     */
    public function valueById($id, $column)
    {
        return $this->model->where('id', $id)->value($column);
    }

    public function firstById($id, $columns = ['*'])
    {
        return $this->model->where('id', $id)->first($columns);
    }

    public function first($filter = [], $columns = ['*'])
    {
        $result = $this->model;
        if ($relation = array_get($filter, 'relation'))
        {
            $result = $this->scopeRelation($result, $relation);
        }
        if ($where = array_get($filter, 'where'))
        {
            $result = $this->scopeWhere($result, $where);
        }
        if ($order = array_get($filter, 'order'))
        {
            $result = $this->scopeOrder($result, $order);
        }

        return $result->first($columns);
    }

    /**
     * Note:
     * @param $id
     * @param array $filter
     * @param string[] $columns
     * @return mixed
     */
    public function findOneById($id, $filter = [], $columns = ['*'])
    {
        $result = $this->model;
        if (!empty($filter))
        {
            $relation = array_get($filter, 'relation');
            $where    = array_get($filter, 'where');

            if ($relation)
            {
                $result = $this->scopeRelation($result, $relation);
            }
            if ($where)
            {
                $result = $this->scopeWhere($result, $where);
            }
        }

        return $result->find($id, $columns);
    }

    /**
     * Note:
     * @param array $filter
     * @param string[] $columns
     * @param false $paginate
     * @return mixed
     */
    public function getAllRecord($filter = [], $columns = ['*'], $paginate = false)
    {
        $result = $this->model;
        if (!empty($filter))
        {
            $relation   = $filter['relation'] ?? null;
            $join       = $filter['join'] ?? null;
            $whereNotIn = $filter['whereNotIn'] ?? null;
            $whereIn    = $filter['whereIn'] ?? null;
            $where      = $filter['where'] ?? null;
            $orWhere    = $filter['orWhere'] ?? null;
            $order      = $filter['order'] ?? null;
            $limit      = $filter['limit'] ?? null;
            $between    = $filter['between'] ?? null;
            $whereDate  = $filter['whereDate'] ?? null;
            $whereMonth = $filter['whereMonth'] ?? null;
            if ($relation)
            {
                $result = $this->scopeRelation($result, $relation);
            }
            if ($join)
            {
                $result = $this->scopeJoin($result, $join);
            }
            if ($whereIn)
            {
                $result = $this->scopeWhereIn($result, $whereIn);
            }
            if ($whereNotIn)
            {
                $result = $this->scopeWhereNotIn($result, $whereNotIn);
            }
            if ($where)
            {
                $result = $this->scopeWhere($result, $where);
            }
            if ($orWhere)
            {
                $result = $this->scopeOrWhere($result, $orWhere);
            }
            if ($order)
            {
                $result = $this->scopeOrder($result, $order);
            }
            if ($between)
            {
                $result = $this->scopeBetween($result, $between);
            }
            if ($whereDate)
            {
                $result = $this->scopeWhereDate($result, $whereDate);
            }
            if ($whereMonth)
            {
                $result = $this->scopeWhereMonth($result, $whereMonth);
            }
            if ($limit)
            {
                $result = $result->limit($limit);
            }
        }

        return $paginate ? $result->paginate($paginate, $columns) : $result->get($columns);
    }
}
