<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$clientId = 3;
$clientSecret = '8ACEOdOjfKpma8j6M4shQif9Ak1L8FWNrGePzy11';

Route::redirect('/', '/products')->name('root');
Route::get('products', 'ProductsController@index')->name('products.index');

Auth::routes(['verify' => true]);

Route::group([
    'middleware' => ['auth', 'verified']
], function() {
    Route::get('user_addresses','UserAddressesController@index')->name('user_addresses.index');
    Route::get('user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
    Route::post('user_addresses', 'UserAddressesController@store')->name('user_addresses.store');
    Route::get('user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');
    Route::put('user_addresses/{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
    Route::delete('user_addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');
});

//测试oauth
// 第三方登陆，重定向
Route::get('/apidemo/login',
    function (\Illuminate\Http\Request $request) use ($clientId) {
        $request->session()->put('state', $state = Str::random(40));

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => 'http://shop.test/auth/callback',
            'response_type' => 'code',
            'scope' => '*',
            'state' => $state,
        ]);

        return redirect('http://apidemo.test/oauth/authorize?'.$query);
    });

// 回调地址，获取 code，并随后发出获取 token 请求
Route::view('/auth/callback', 'auth_callback');

Route::post('/get/token', function (\Illuminate\Http\Request $request) use (
    $clientId,
    $clientSecret
) {
    // csrf 攻击处理
    $state = $request->session()->pull('state');
    throw_unless(
        strlen($state) > 0 && $state === $request->params['state'],
        InvalidArgumentException::class
    );


    $response
        = (new \GuzzleHttp\Client())->post('http://apidemo.test/oauth/token', [
        'form_params' => [
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => 'http://shop.test/auth/callback',
            'code' => $request->params['code'],
        ],
    ]);

    return json_decode((string)$response->getBody(), true);
});
// 刷新 token
Route::view('/refresh/page', 'refresh_page');
Route::post('/refresh', function (\Illuminate\Http\Request $request) use (
    $clientId,
    $clientSecret
) {
    $http = new GuzzleHttp\Client;
    $response = $http->post('http://apidemo.test/oauth/token', [
        'form_params' => [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->params['refresh_token'],
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ],
    ]);

    return json_decode((string)$response->getBody(), true);
});

Route::get('/yinshi', function () use(
    $clientId
){
    $query = http_build_query([
        'client_id' => $clientId,
        'redirect_uri' => 'http://shop.test/auth/callback',
        'response_type' => 'token',
        'scope' => '',
    ]);

    return redirect('http://apidemo.test/oauth/authorize?'.$query);
});