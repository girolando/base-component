<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 29/03/2016
 * Time: 15:07
 */

namespace App\Services\Componentes;


abstract class BaseComponentService
{
    protected abstract function _init($params = []);

    public function init($params = [])
    {
        $customView = $this->_init($params);
        return view('Services.Componentes.BaseComponent.init', ['customView' => $customView]);
    }

}