<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JD\Cloudder\Facades\Cloudder;

class UserController extends CoreController
{
	public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function account()
    {
    	$user_data =  $this->getUserProfile();
    	$image = $user_data['image'];
    	if($image !== null)
    	{
    		$tmp_image = str_replace(Env('URL_CLOUD'), "", $image);
    		$image = Env('URL_CLOUD').'c_scale,h_153,w_263/'.$tmp_image;
    	}
    	$user_data['image'] = $image;

    	$breadcrumb = array(
            array("name" => 'Home', 'url' => 'home'),
            array("name" => 'Account', 'url' => 'my-account'),
        );
    	return view('user.account', ['user_data' => $user_data, 'breadcrumb' => $breadcrumb]);
    }

    public function dana_simpanan_anggota()
    {
        $response = get_api_response('member/dana/simpanan_anggota');
        if($response->code != 200 OR ! isset($response->data->dana))
            return response()->json(['dana' => 0, 'reformat_dana' => 0]);

            return response()->json(['dana' => $response->data->dana, 'reformat_dana' => number_format($response->data->dana)]);
    }

    public function ajaxDataMenu()
    {
        $user_data =  $this->getUserProfile();
        $html = view('user.ajaxHeaderMenu')->with(['user_data' => $user_data])->render();
        return response()->json(['html' => $html]);
    }

    public function isi_saldo()
    {
        $user_data =  $this->getUserProfile();    
        return view('user.isi_saldo', ['user_data' => $user_data, 'menu_side_bar' => 'account']);
    }

    public function store_isi_saldo(Request $request)
    {        
        $data_post['saldo'] = $request->saldo ? (int) str_replace(".", "", $request->saldo)  : 0;
        $data_post['type'] = $request->jenis_pengisian ? (int) $request->jenis_pengisian  : 1;
        $data_post['type_transaction'] = 1;
        $response = get_api_response('isi/saldo', 'POST', [], $data_post);
        if($response->code != 200)
            return redirect('isi/saldo');

        return redirect('top-up/saldo/'.$response->data->transaction_code);
    }

    public function top_up($transaction_code, Request $request)
    {
        $user_data =  $this->getUserProfile();
        $response = get_api_response('transaction/'.$transaction_code.'/detail');
        if($response->code != 200 OR count($response->data) < 1)
            return redirect('home');
        
        return view('user.detail_top_up', ['user_data' => $user_data, 'menu_side_bar' => 'account', 'top_up' => $response->data]);   
    }

    public function profile(Request $request)
    {
        $user_data =  $this->getUserProfile();

        $breadcrumb = array(
            array("name" => 'Home', 'url' => 'home'),
            array("name" => 'Account', 'url' => 'profile'),
        );
    
        return view('user.profile', ['user_data' => $user_data, 'breadcrumb' => $breadcrumb, 'menu_side_bar' => 'account']);
    }

    public function pending_top_up()
    {
        $response = get_api_response('pending/top_up');
        $total = 0;
        if($response->code != 0 AND count($response->data) < 1)
            return response()->json(['total' => number_format($total)]);

        foreach ($response->data as $key => $value) {
            $total = $total + $value->price_total;
        }

        return response()->json(['total' => number_format($total)]);
    }

    public function edit()
    {
        $start_year = config('constants.start_year');
        $year = [];
        for ($i=$start_year; $i <= date('Y'); $i++) { 
            $year[$i] = $i;
        }
    	$user_data =  $this->getUserProfile();
        $date = explode("-", $user_data['birth']);
        if(count($date) === 0)
        {
            $date[0] = "";
            $date[1] = "";
            $date[2] = "";
        }

        if($user_data['gender'] === 1)
        {
            $gM =  true;
            $gF = false;
        }else{
            $gF =  true;
            $gM = false;
        }

    	return view('user.edit-account', ['user_data' => $user_data, 'date_day' => config('constants.date_day') , 'date_month' => config('constants.date_month'), 'date_year' => $year, 'date' => $date, 'gF' => $gF, 'gM' => $gM, 'menu_side_bar' => 'account']);
    }

    public function upload_image(Request $request)
    {
        $result_upload = Cloudder::upload($request->file('photo')->getPathName());
        $result = $result_upload->getResult();

        $data_post = [
            'photo'     => $result['secure_url']
        ];

        $response = get_api_response('member/edit/image', 'POST', [], $data_post);
        echo json_encode($response);
    }

    public function store_profile(Request $request)
    {
        $data_post = [
            'birth'     => strtotime($request->birth),
            'gender'     => $request->gender,
            'name'     => $request->name
        ];

        $response = get_api_response('member/edit/profile', 'POST', [], $data_post);
        echo json_encode($response);
    }

    public function list_trasaction(Request $request)
    {
        $transaction = get_api_response('transaction/list');
        $user_data =  $this->getUserProfile();

        return view('user.list_transaction', ['user_data' => $user_data, 'transaction' => $transaction->data, 'menu_side_bar' => 'transaction']);
    }

    public function place_list()
    {
        $user_data =  $this->getUserProfile();
        $place_pickup = get_api_response('member/place/list');
        return view('user.edit_pickup_place', ['user_data' => $user_data, 'place_pickup' => $place_pickup->data, 'menu_side_bar' => 'place_list_user' ]);
    }

    public function ajax_place_list()
    {
        $place_pickup = get_api_response('member/place/list');
        return response()->json(['data'=> $place_pickup->data, 'code' => $place_pickup->code]);
    }

    public function store_place(Request $request)
    {
        $data_post = [
            'place_name'                => $request->place_alias,
            'recipient_name'            => $request->recipient_name,
            'phone_number_recipient'    => $request->phone,
            'district_id'               => $request->district,
            'postal_code'               => $request->postal_code,
            'addres'                    => $request->address,
        ];

        $response = get_api_response('member/place', 'POST', [], $data_post);

        if($response->code !== 200)
            return response()->json(array('success' => false));
        
        // $place_pickup = get_api_response('member/place/list');
        // $returnHTML = view('user.ajax_place_view')->with('place_pickup', $place_pickup->data)->render();
        
        return response()->json(array('success' => true));
    }

    public function put_place(Request $request)
    {
        $data_post = [
            'id'                        => $request->id,
            'place_name'                => $request->place_alias,
            'recipient_name'            => $request->recipient_name,
            'phone_number_recipient'    => $request->phone,
            'district_id'               => $request->district,
            'postal_code'               => $request->postal_code,
            'addres'                    => $request->address,
        ];

        $response = get_api_response('member/place', 'PUT', [], $data_post);

        if($response->code !== 200)
            return response()->json(array('success' => false));
        
        // $place_pickup = get_api_response('member/place/list');
        // $returnHTML = view('user.ajax_place_view')->with('place_pickup', $place_pickup->data)->render();
        
        return response()->json(array('success' => true));
    }

    public function destroy_place(Request $request)
    {
        $data_post = [
            'id'   => $request->delete_id
        ];

        $response = get_api_response('member/place', 'DELETE', [], $data_post);

        if($response->code !== 200)
            return response()->json(array('success' => false));
        
        // $place_pickup = get_api_response('member/place/list');
        // $returnHTML = view('user.ajax_place_view')->with('place_pickup', $place_pickup->data)->render();

        return response()->json(array('success' => true));
    }

    public function get_place(Request $request)
    {
        $data_post = [
            'id'   => $request->id
        ];

        $response = get_api_response('member/place/get', 'POST', [], $data_post);
        return response()->json($response->data);
    }
}