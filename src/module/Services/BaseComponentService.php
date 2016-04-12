<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 29/03/2016
 * Time: 15:07
 */
namespace Andersonef\BaseComponent\Services;


abstract class BaseComponentService
{
    protected abstract function _init($params = []);

    public function init($params = [])
    {
        $customView = $this->_init($params);
        return view('_GHBaseComponent::Services.BaseComponentService.init', ['customView' => $customView]);
    }

}