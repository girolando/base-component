<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 12/04/2016
 * Time: 11:38
 */

namespace Andersonef\BaseComponent\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

class BaseComponentProvider extends RouteServiceProvider
{
    public function boot(Router $router)
    {
        Lang::addNamespace('_GHBaseComponent', __DIR__.'/../../resources/lang');
        View::addNamespace('_GHBaseComponent', __DIR__.'/../../resources/views');
        parent::boot($router);
    }

}