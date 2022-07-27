<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Models;

use Illuminate\Support\Str;

class Permission extends BaseModel
{
    /**
     * $this->name_to_string
     *
     * @return string
     */
    public function getNameToStringAttribute(): ?string
    {
        $name = $this->name;
        $routes = explode('.', $name);
        $count = count($routes);
        if($count >= 2){
            $method = $routes[$count - 1];
            if($count == 2){
                $choice = trans_choice("choice.".Str::pluralStudly($routes[0]), 2);
                $custom = "permissions.{$name}";
                if(trans_has($custom)){
                    return __($custom, ['name' => $choice]);
                }
                return __("replace.$method", ['name' => $choice]);
            }
            unset($routes[$count - 1]);
            $locale = app()->getLocale();
            if($locale == 'ar'){
                $routes = array_reverse($routes);
            }
            $name = '';
            foreach($routes as $k => $route){
                $name .= ($name ? ' ' : '');
                $n = ($locale != 'ar' && (count($routes) - 1) == $k) || ($locale == 'ar' && (count($routes) - 1) != $k) ? 2 : 1;

                $choiceKey = "choice.".Str::pluralStudly($route);
                $choice = trans_has($choiceKey) ? trans_choice($choiceKey, $n) : $route;
                if(Str::startsWith($choice, ($st = 'ال'))){
                    if($locale == 'ar' && (count($routes) - 1) != $k){
                        $choice = Str::after($choice, $st);
                    }
                }
                $name .= $choice;
            }
            $k = "permissions.{$method}";
            if(trans_has($k)){
                return __($k, ['name' => $name]);
            }

            $k = "replace.{$method}";
            if(trans_has($k)){
                return __($k, ['name' => $name]);
            }
            //return __("replace.{$lang}", ['name' => $choice]);
            //d($name);
            //$lang = $routes[$count - 1];
            //$model = Str::pluralStudly($routes[$count - 2]);
            //$choice = trans_choice("choice.{$model}", 2);
            //$k = "replace.{$lang}";
            //if (trans_has($k)) {
            //    return __($k, ['name' => $choice]);
            //}
        }

        $custom = "permissions.{$name}";
        if(trans_has($custom)){
            return __($custom, ['name' => $name]);
        }

        return $name;
    }
}
