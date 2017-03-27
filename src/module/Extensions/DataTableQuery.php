<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 23/02/2016
 * Time: 15:11
 */
namespace Girolando\BaseComponent\Extensions;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

class DataTableQuery
{
    private static $instances = [];

    private $db;
    private $filters;
    private $request;
    private $isSimpleRequest = false;

    private function __construct($name){
        $this->request = Request::capture();
        $this->db = app(DatabaseManager::class);
        if(!$this->request->has('_DataTableQuery')) {
            $this->isSimpleRequest = true;
            return;
        }
        $this->filters = json_decode($this->request->_DataTableQuery[$name])->$name;

    }


    public function fetchSelectedItems(QueryBuilder $builder)
    {
        if($this->isSimpleRequest) return $builder;
        $self = $this;
        $this->filters->items[] = -1;
        //faço a busca de acordo com a palavra pesquisada, caso tenha uma:
        if($this->filters->searchString){
            if(count($this->filters->columns) > 0){
                $builder->where(function($q) use ($self, $builder){
                    foreach($this->filters->columns as $column){
                        if(!$column->bSearchable) continue;
                        $builder->orWhereRaw($column->name." LIKE '%".$self->filters->searchString."%'");
                    }
                });
            }
        }
        if($this->filters->checkedAll == 1){
            $builder->whereNotIn($this->filters->idField, $this->filters->items);
            return $builder;
        }
        $builder->whereIn($this->filters->idField, $this->filters->items);
        return $builder;
    }

    public static function getInstance($name)
    {
        if(!isset(self::$instances[$name])) self::$instances[$name] = new DataTableQuery($name);
        return self::$instances[$name];
    }

    public function getFilters()
    {
        if($this->isSimpleRequest) return (object) [];
        return $this->filters->filters;
    }


    public function apply(QueryBuilder $builder)
    {
        if($this->isSimpleRequest) return $builder;

        if(!$this->request->has('_DatatableQuery') || !isset($this->filters->idField)) {
            $builder->addSelect($this->db->raw('0 as _checked'));
            return $builder;
        }

        $this->filters->items[] = -1;
        if($this->filters->checkedAll) {
            $builder->addSelect($this->db->raw('(case when ' . $this->filters->idField . ' IN ('.implode(',', $this->filters->items).') then 0 else 1 end) as _checked'));
            return $builder;
        }
        $builder->addSelect($this->db->raw('(case when ' . $this->filters->idField . ' IN ('.implode(',', $this->filters->items).') then 1 else 0 end) as _checked'));
        return $builder;
    }




}