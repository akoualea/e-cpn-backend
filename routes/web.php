














<?php

Route::get('/', function () {
    return response()->json([
        'status' => 'Connecté',
        'service' => 'Backend E-CPN Bénin',
        'message' => 'Le moteur de l\'application fonctionne parfaitement.'
    ]);
});