<?php 
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Response;

class CatchAllOptionsRequestsProvider extends ServiceProvider {
  public function register() {
    $request = app('request');
    if ($request->isMethod('OPTIONS')) {
    	header('Access-Control-Allow-Origin: *');
    	header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
    	header('Access-Control-Allow-Headers: *');
		return (new Response('', 200));
    }
  }
}