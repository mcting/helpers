<?php


namespace MuCTS\Helpers;


use Illuminate\Support\ServiceProvider;
use MuCTS\Helpers\Commands\ModelMakeCommand;

class HelpersServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ModelMakeCommand::class
            ]);
        }
    }
}