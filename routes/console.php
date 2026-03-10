<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:task')
    ->everyMinute()
    ->onOneServer()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('queue:deadline')
    ->daily()
    ->onOneServer()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('queue:deletezip')
    ->daily()
    ->onOneServer()
    ->withoutOverlapping()
    ->runInBackground();
