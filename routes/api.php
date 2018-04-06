<?php


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'auth:api', 'namespace' => 'Api\V1', 'prefix' => 'v1'], function () {
    // Me
    Route::get('/me', 'UsersController@me')->name('me');

    // Bridges
    Route::resource('bridges', 'BridgeController');
    Route::patch('bridges/{bridge}/name', 'BridgeController@update')->name('bridges.updateName');

    Route::resource('bridges.sections', 'SectionController');
    Route::patch('bridges/{bridgeId}/sections/{sectionId}/updateTitle', 'SectionController@updateTitle');
    Route::patch('bridges/{bridgeId}/sections/{sectionId}/updateDescription', 'SectionController@updateDescription');
    Route::post('/bridges/{bridgeId}/icons', 'SourceFileController@storeIcon');
    Route::post('/bridges/{bridgeId}/icons/{iconId}/convert', 'SourceFileController@addIconConverted');

    // FONTS
    Route::get('/fonts/search/{search}', 'FontsController@search')->name('fonts.search');
    Route::post('/bridges/{bridge}/fonts', 'FontsController@store')->name('fonts.store');
    Route::delete('/bridges/{bridge}/fonts/{font}', 'FontsController@destroy')->name('fonts.destroy');

    Route::post('/bridges/{bridgeId}/colors', 'ColorsController@store');
    Route::post('/bridges/{bridge}/bulk-colors', 'ColorsController@storeBulkColors')->name('colors.storeBulk');
    Route::patch('/bridges/{bridgeId}/colors/{colorId}', 'ColorsController@update');
    Route::delete('/bridges/{bridgeId}/colors/{colorId}', 'ColorsController@destroy');


    Route::delete('/bridges/{bridgeId}/icons/{iconId}', 'SourceFileController@deleteIcon');
    Route::delete('/bridges/{bridgeId}/images/{iconId}', 'SourceFileController@deleteImage');
    Route::post('/bridges/{bridgeId}/icons/{iconId}/converted', 'SourceFileController@addIconConverted');
    Route::post('/bridges/{bridgeId}/icons/{iconId}/filename', 'SourceFileController@updateIconFile');

    Route::patch('/bridges/{bridge}/slug', 'BridgeController@updateSlug')->name('bridges.updateSlug');
    Route::post('/bridges/{bridgeId}/images/', 'SourceFileController@storeImage');
    Route::post('/bridges/{bridgeId}/images/{imageId}/converted', 'SourceFileController@addImageConverted');
    Route::post('/bridges/{bridgeId}/images/{imageId}/filename', 'SourceFileController@updateImageFile');

    Route::post('/order/{type}/{objectId}/{newOrder}', 'OrderController@changedOrderOnSameSection');
    Route::post('/changeSection/{type}/{objectid}/{newSection}', 'OrderController@changedSection');
});
