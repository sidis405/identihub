<?php

Route::group(['middleware' => 'auth:api', 'namespace' => 'Api\V1', 'prefix' => 'v1'], function () {
    // Me
    Route::get('/me', 'UsersController@me')->name('me');

    // Bridges
    Route::patch('bridges/{bridge}/name', 'BridgeController@update')->name('bridges.updateName');
    Route::patch('/bridges/{bridge}/slug', 'BridgeController@updateSlug')->name('bridges.updateSlug');
    Route::resource('bridges', 'BridgeController');

    // FONTS
    Route::get('/fonts/search/{search}', 'FontsController@search')->name('fonts.search');
    Route::resource('/bridges/{bridge}/fonts', 'FontsController')->only('store', 'destroy');

    // COLORS
    Route::post('/bridges/{bridge}/bulk-colors', 'ColorsController@storeBulkColors')->name('colors.storeBulk');
    Route::resource('/bridges/{bridge}/colors', 'ColorsController')->only('store', 'update', 'destroy');

    // SECTIONS
    Route::resource('/bridges/{bridge}/sections', 'SectionsController');
    Route::patch('bridges/{bridge}/sections/{section}/updateTitle', 'SectionsController@updateTitle')
    ->name('sections.updateTitle');
    Route::patch('bridges/{bridge}/sections/{section}/updateDescription', 'SectionsController@updateDescription')
    ->name('sections.updateDescription');

    // ICONS
    Route::post('/bridges/{bridgeId}/icons', 'IconsController@storeIcon');
    Route::delete('/bridges/{bridgeId}/icons/{iconId}', 'IconsController@deleteIcon');
    Route::post('/bridges/{bridgeId}/icons/{iconId}/converted', 'IconsController@addIconConverted');
    Route::post('/bridges/{bridgeId}/icons/{iconId}/filename', 'IconsController@updateIconFile');
    Route::post('/bridges/{bridgeId}/icons/{iconId}/convert', 'IconsController@addIconConverted');

    // IMAGES
    Route::post('/bridges/{bridgeId}/images/', 'ImagesController@storeImage');
    Route::delete('/bridges/{bridgeId}/images/{iconId}', 'ImagesController@deleteImage');
    Route::post('/bridges/{bridgeId}/images/{imageId}/converted', 'ImagesController@addImageConverted');
    Route::post('/bridges/{bridgeId}/images/{imageId}/filename', 'ImagesController@updateImageFile');

    // ORDER
    Route::post('/order/{type}/{objectId}/{newOrder}', 'OrderController@changedOrderOnSameSection')->name('order.same');
    Route::post('/changeSection/{type}/{objectid}/{newSection}', 'OrderController@changedSection')->name('order.changed');
});
