<?php
namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Validator;

class RegisterController extends Controller
{
    public function index()
    {
    	$category =  get_api_response('category');
        return view('register.index', ['category' => $category->data]);
    }

    public function storeData(Request $request)
    {
    	
    	$rules = [
	        'nama_lengkap'	=> 'required|min:3',
	        'email' 		=> 'required|email',
	        'password' 		=> 'required',
	        'no_telp' 		=> 'required'
        ];

    	$validator = Validator::make(
    		$request->all(),
    		$rules
		);

		if ($validator->fails()){
			$category =  get_api_response('category');
			
			return view('register.index', ['message_error' => $validator->errors()->all(), 'category' => $category->data]);
		}
							
		$username = str_replace(' ', '_', $request->nama_lengkap);
		$username.= date('dmyhis');
		$body = [
			'name' 		=> $request->nama_lengkap,
			'email' 	=> $request->email,
			'username' 	=> $username,
			'address' 	=> '',
			'phone' 	=> $request->no_telp,
			'password' 	=> $request->password
		];

		$response = get_api_response('member/register', 'POST', [], $body);
		$code = $response->code;
		$message = $response->message;
		$error = $response->error;
		
		if($code != 200 OR $error){
			if($code == 409)
				return view('register.exists', ['message' => $message, 'input' => $request]);
			else{
				$category =  get_api_response('category');
				return view('register.index', ['message_error' => $message, 'category' => $category->data]);
			}
		}

		$request->session()->put('token', $response->meta->token);
		return redirect()->route('home');
    }
}