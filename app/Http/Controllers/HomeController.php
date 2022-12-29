<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\SendPushNotification;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(){

        return view('home');
    }


    public function saveToken(Request $request)
    {
        auth()->user()->update(['device_token'=>$request->token]);
        return response()->json(['token saved successfully.']);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function sendNotification(Request $request){

        $request->validate([

            'title' => 'required',
            'body' => 'required',
            'user_id' => 'required|exists:users,id'
        ]);
//        $firebaseToken = User::whereNotNull('device_token')->whereIn('id',$request->user_id)->pluck('device_token')->all(); //list of users
        $firebaseToken = User::whereNotNull('device_token')->where('id',$request->user_id)->pluck('device_token')->all();

        $SERVER_API_KEY = 'AAAAQQckzyU:APA91bHDc753hYb9H80I4_d75bA9fCbhrq_tFyfQQgVITMBzzzoumyR3F88BjzHRP02dxFdvhfE1bVr3wklLSKi1zMhSjUvH_xzU1570r-aV_tzSAWJcIijePBxq5aKHhaA6g2q_EqDz';

        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $request->title,
                "body" => $request->body,
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

//        dd($response);

        return redirect()->back()->with('success','Notification send successfully');
    }
}
