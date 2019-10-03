<?php

Route::get('/', function()
{
    return view('welcome');
});

Route::post('/' , 'S3Controller@upload');

Route::get   ('file/download','S3Controller@download');

Route::get     ('file/list',          'S3Controller@list');
Route::get     ('file/list/objects',  'S3Controller@listObjects');
Route::post    ('file/copy' ,         'S3Controller@copy');
Route::delete  ('file/delete/{id}' ,       'S3Controller@delete');
