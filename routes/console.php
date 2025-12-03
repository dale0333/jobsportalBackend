<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:task')
    ->everyMinute()
    ->onOneServer()
    ->withoutOverlapping()
    ->runInBackground();
