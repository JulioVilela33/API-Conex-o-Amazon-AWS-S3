<?php

Route::get('file/listfiles', 'S3Controller@listFiles');
Route::get('file/listdir', 'S3Controller@listDir');
Route::get('file/download', 'S3Controller@download');

Route::post('file/upload', 'S3Controller@upload');
Route::post('file/delete/file', 'S3Controller@delete');
Route::post('file/delete/directory', 'S3Controller@deleteDirectory');
Route::post('file/copy', 'S3Controller@copy');
Route::post('file/move', 'S3Controller@move');
Route::post('file/mkdir', 'S3Controller@makeDirectory');
