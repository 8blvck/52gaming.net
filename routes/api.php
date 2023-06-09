<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'noadmin', 'middleware' => 'cors'], function() {
    Route::post('/storage/{disk}',                  "NoAdminController@storeFile");
    Route::get('/interface/tests',                  "NoAdminController@tests");
    Route::get('/interface/settings',               "NoAdminController@getSettings");
    Route::post('/interface/settings',              "NoAdminController@saveSettings");
    Route::delete('/interface/settings/{id}',       "NoAdminController@removeSettings");
    Route::get('/interface/menu',                   "NoAdminController@menu");
    Route::get('/interface/pricelists',             "NoAdminController@getPricelists");
    Route::post('/interface/prices_boost',          "NoAdminController@setBoostPrices");
    Route::post('/interface/prices_boost_type/{id}',"NoAdminController@updateBoostType");
    Route::post('/interface/prices_boost_type',     "NoAdminController@addBoostType");
    Route::delete('/interface/prices_boost_type/{id}',"NoAdminController@removeBoostType");
    Route::post('/interface/prices_cali',           "NoAdminController@setCalibrationPrices");
    Route::post('/interface/prices_lp',             "NoAdminController@setLPPrices");
    Route::post('/interface/prices_training',       "NoAdminController@setTrainingPrices");
    Route::post('/interface/prices_medal',          "NoAdminController@addMedalPrice");
    Route::post('/interface/prices_medals',         "NoAdminController@setMedalPrices");
    Route::post('/interface/prices_medal/{id}',     "NoAdminController@updateMedalPrice");
    Route::delete('/interface/prices_medal/{id}',   "NoAdminController@removeMedalPrice");
    Route::get('/interface/translate',		        "NoAdminController@translate");
    Route::get('/interface/translations',           "NoAdminController@getTranslations");
    Route::get('/interface/heroes',	                "NoAdminController@getHeroes");
    Route::post('/interface/translations',	        "NoAdminController@setTranslations");
    Route::get('/interface/configuration_steps',    "NoAdminController@getConfSteps");
    Route::post('/interface/configuration_steps',   "NoAdminController@updateConfSteps");
    Route::get('/order_types',                      "NoAdminController@getOrderTypes");
    Route::post('/order_types/{id}',                "NoAdminController@updateOrderType");
    Route::get('/order_types/{id}',                 "NoAdminController@getOrderType");
    Route::delete('/order_types/{id}',              "NoAdminController@removeOrderType");
    Route::post('/order_types',                     "NoAdminController@addOrderType");
    Route::get('/games',                            "NoAdminController@getGames");
    Route::post('/games/{id}',                      "NoAdminController@updateGame");
    Route::get('/games/{id}',                       "NoAdminController@getGame");
    Route::delete('/games/{id}',                    "NoAdminController@removeGame");
    Route::post('/games',                           "NoAdminController@addGame");
    Route::get('/pages',					        "NoAdminController@getPages");
    Route::post('/pages/{id}',				        "NoAdminController@updatePage");
    Route::get('/pages/{id}',				        "NoAdminController@getPage");
    Route::delete('/pages/{id}',			        "NoAdminController@removePage");
    Route::post('/pages',					        "NoAdminController@addPage");
    Route::get('/news',						        "NoAdminController@getPosts");
    Route::post('/news',					        "NoAdminController@addPost");
    Route::post('/news/comments',			        "NoAdminController@addPostComment");
    Route::get('/news/{id}',				        "NoAdminController@getPost");
    Route::delete('/news/{id}',				        "NoAdminController@removePost");
    Route::delete('/news/comments/{id}',	        "NoAdminController@removePostComment");
    Route::post('/news/{id}',				        "NoAdminController@updatePost");
    Route::post('/news/comments/{id}',		        "NoAdminController@updatePostComment");
    Route::get('/faq',                              "NoAdminController@getFAQs");
    Route::post('/faq/{id}',                        "NoAdminController@updateFAQ");
    Route::get('/faq/{id}',                         "NoAdminController@getFAQ");
    Route::delete('/faq/{id}',                      "NoAdminController@removeFAQ");
    Route::post('/faq',                             "NoAdminController@addFAQ");
    Route::get('/advantages',                       "NoAdminController@getAdvantages");
    Route::post('/advantages/{id}',                 "NoAdminController@updateAdvantage");
    Route::get('/advantages/{id}',                  "NoAdminController@getAdvantage");
    Route::delete('/advantages/{id}',               "NoAdminController@removeAdvantage");
    Route::post('/advantages',                      "NoAdminController@addAdvantage"); 
    Route::get('/slides',                           "NoAdminController@getSlides");
    Route::post('/slides/{id}',                     "NoAdminController@updateSlide");
    Route::get('/slides/{id}',                      "NoAdminController@getSlide");
    Route::delete('/slides/{id}',                   "NoAdminController@removeSlide");
    Route::post('/slides',                          "NoAdminController@addSlide");  
    Route::get('/works',                            "NoAdminController@getWorks");
    Route::post('/works/{id}',                      "NoAdminController@updateWork");
    Route::get('/works/{id}',                       "NoAdminController@getWork");
    Route::delete('/works/{id}',                    "NoAdminController@removeWork");
    Route::post('/works',                           "NoAdminController@addWork");  
    Route::get('/reviews',                          "NoAdminController@getReviews");
    Route::post('/reviews/{id}',                    "NoAdminController@updateReview");
    Route::get('/reviews/{id}',                     "NoAdminController@getReview");
    Route::delete('/reviews/{id}',                  "NoAdminController@removeReview");
    Route::post('/reviews',                         "NoAdminController@addReview");
    Route::get('/staff',                            "NoAdminController@getStaffs");
    Route::post('/staff/{id}',                      "NoAdminController@updateStaff");
    Route::get('/staff/{id}',                       "NoAdminController@getStaff");
    Route::delete('/staff/{id}',                    "NoAdminController@removeStaff");
    Route::post('/staff',                           "NoAdminController@addStaff");
    Route::post('/authorize',                       "NoAdminController@login");
    Route::get('/users',                            "NoAdminController@getUsers");
    Route::get('/users/{id}',                       "NoAdminController@getUser");
    Route::post('/users/{id}',                      "NoAdminController@updateUser");
    Route::post('/users',                           "NoAdminController@addUser");
    Route::delete('/users/{id}',                    "NoAdminController@removeUser");
    Route::get('/user_types',                       "NoAdminController@getUserTypes");
    Route::post('/user_types',                      "NoAdminController@createUserType");
    Route::post('/user_types/{id}',                 "NoAdminController@updateUserType");
    Route::delete('/user_types/{id}',               "NoAdminController@removeUserType");
    Route::get('/user_types/{id}',                  "NoAdminController@getUserType");
    Route::get('/promocodes',                       "NoAdminController@getPromocodes");
    Route::post('/promocodes/{id}',                 "NoAdminController@updatePromocode");
    Route::get('/promocodes/{id}',                  "NoAdminController@getPromocode");
    Route::delete('/promocodes/{id}',               "NoAdminController@removePromocode");
    Route::post('/promocodes',                      "NoAdminController@addPromocode"); 
    Route::get('/ads',                              "NoAdminController@getAds");
    Route::post('/ads/{id}',                        "NoAdminController@updateAds");
    Route::get('/ads/{id}',                         "NoAdminController@getAd");
    Route::delete('/ads/{id}',                      "NoAdminController@removeAds");
    Route::post('/ads',                             "NoAdminController@addAds"); 
});