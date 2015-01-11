<?php

namespace Minify;

use Config;

class MinifyServiceProvider extends \CeesVanEgmond\Minify\MinifyServiceProvider {

    public function register()
    {
        $this->app['Minify'] = $this->app->share(function($app)
        {
            return new Minify(
                array(
                    'css_build_path' => Config::get('minify::css_build_path'),
                    'js_build_path' => Config::get('minify::js_build_path'),
                    'ignore_environments' => Config::get('minify::ignore_environments'),
                    'base_url' => Config::get('minify::base_url'),
                ),
                $app->environment()
            );
        });
    }

}