<?php

use App\Console\Commands\ProcessPendingWebhooks;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ProcessPendingWebhooks::class, ['--limit=3000'])->everyTwoMinutes();
