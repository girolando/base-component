<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 02/08/2016
 * Time: 11:21
 */

namespace Girolando\BaseComponent\Engines;


use Andersonef\Repositories\Abstracts\ServiceAbstract;
use Girolando\BaseComponent\Exceptions\GirolandoComponentException;
use Girolando\BaseComponent\Extensions\DataTableQuery;
use Illuminate\Http\Request;

class DatasetEngine
{
    protected $service;
    protected $dataTableQueryName;

    public function __construct(ServiceAbstract $serviceAbstract)
    {
        $this->service = $serviceAbstract;
    }

    public function usingDataTableQuery($name)
    {
        $this->dataTableQueryName = $name;
        return $this;
    }

    public function createDataset(array $searchableFields)
    {
        if(!$this->dataTableQueryName) throw new GirolandoComponentException('Chamada ao createDataset sem informar antes o dataTAbleQueryName pelo método usingDataTableQuery()');
        
        $queryBuilder = $this->service->getQuery();
        $dataTableQuery = DataTableQuery::getInstance($this->dataTableQueryName);
        $filters = (array) $dataTableQuery->getFilters();


        if($filters){
            $nfilters = [];
            foreach($filters as $filter => $value){
                if(!in_array($filter, $searchableFields)) continue;
                $nfilters[$filter] = $value;
            }
            if($nfilters) {
                $queryBuilder = $this->service->findBy($nfilters);
                $queryBuilder = $queryBuilder->getQuery();
            }
        }
        $queryBuilder->select(['*']);
        $dataset = $dataTableQuery->apply($queryBuilder);

        $request = Request::capture();
        if($request->has('customFilters')){
            $customFilters = $request->get('customFilters');
            $dataset->where( function($query) use($customFilters) {
                foreach($customFilters as $filter => $value){
                    $query->orWhere($filter, 'like', $value);
                }
            });
        }

        return $dataset;
    }
}