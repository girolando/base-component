<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 29/03/2016
 * Time: 15:07
 */
namespace Girolando\BaseComponent\Services;


abstract class BaseComponentService
{
    protected static $baseJsLoaded = false;
    protected static $loadedComponents = [];
    protected abstract function _init($params = []);

    public function init($params = [])
    {
        $isLoaded = self::$baseJsLoaded;
        self::$baseJsLoaded = true;
        $customView = $this->_init($params);
        $registered = get_class($this);
        $componentLoaded = false;
        if(isset(self::$loadedComponents[$registered])){
            return;
        }
        self::$loadedComponents[$registered] = true;
        return view('_GHBaseComponent::Services.BaseComponentService.init', ['customView' => $customView, 'isLoaded' => $isLoaded, 'componentLoaded' => $componentLoaded]);
    }

}