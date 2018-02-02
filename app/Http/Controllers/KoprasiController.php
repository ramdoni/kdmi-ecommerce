<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class KoprasiController extends CoreController
{
	public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function register()
    {
    	$user_data =  $this->getUserProfile();
    	$category =  get_api_response('category');
    	$categoryInSearch =  get_api_response('category-insearch');
    	$breadcrumb = array(
    		array("name" => 'Home', 'url' => 'home'),
    		array("name" => 'Register Koprasi', 'url' => 'koprasi/register'),
    	);

        return view('koprasi.register', ['categoryInSearch' => $categoryInSearch->data, 'category' => $category->data, 'user_data' => $user_data, 'breadcrumb' => $breadcrumb]);
    }

    public function store(Request $request)
    {
        $rules = [
            'nama_lengkap'  => 'required',
            'regency'       => 'required',
            'alamat_pickup' => 'required',
            'kode_pos'      => 'required'
        ];

        $validator = Validator::make(
            $request->all(),
            $rules
        );

        if ($validator->fails())
            return redirect()->route('koprasi/register');

        $body = [
            'name'              => $request->nama_lengkap,
            'url'               => str_replace(" ", "", $request->nama_lengkap),
            'pickup_address'    => $request->alamat_pickup,
            'kode_pos'          => $request->alamat_pickup,
            'postal_code'       => $request->kode_pos,
            'regency'           => (int) $request->regency,
            'image'             => $request->image,
        ];

        $response = get_api_response('shop/register', 'POST', [], $body);
    }
}