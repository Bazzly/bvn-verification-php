<?php
// src/Laravel/BVNVerificationServiceProvider.php

namespace BVNVerification\Laravel;

use BVNVerification\BVNVerifier;
use Illuminate\Support\ServiceProvider;

class BVNVerificationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/bvn-verification.php', 'bvn-verification'
        );

        $this->app->singleton('bvn-verifier', function ($app) {
            $config = $app['config']['bvn-verification'];
            
            return new BVNVerifier(
                $config['api_key'],
                $config['sandbox_mode'] ?? false,
                $config['mode'] ?? 'json-mock'
            );
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/bvn-verification.php' => config_path('bvn-verification.php'),
            ], 'bvn-verification-config');
        }
    }
}