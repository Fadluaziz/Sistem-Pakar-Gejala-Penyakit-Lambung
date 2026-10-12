<?php

require base_path('app/Features/Authentication/routes_web.php');
// Tambahkan feature lain di sini, misal:
// require base_path('app/Features/DiseaseDetection/routes_web.php');

Route::get('/', function () {
    return redirect('/login');
});
