<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class TestController extends Controller
{
    public function index()
    {
       	return get_api_response('category');
    }
}