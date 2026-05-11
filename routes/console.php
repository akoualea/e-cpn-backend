<?php

use Illuminate\Support\Facades\Schedule;

// Syntaxe pour Laravel 11 uniquement dans routes/console.php
Schedule::command('cpn:send-reminders')->dailyAt('08:00');