<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 22/08/2016
 * Time: 16:41
 */

namespace Girolando\BaseComponent\Contracts;


interface ComponentServiceContract
{
    public function getDataset($datatableQueryName);
    public function getJsonDataset($datatableQueryName);
}