<?php

namespace Tests;

trait CreatesApplication
{
    /**
     * Bootstrap the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }
}
