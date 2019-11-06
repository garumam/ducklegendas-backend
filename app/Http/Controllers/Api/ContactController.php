<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public $successStatus = 200;
    public $errorStatus = 403;

    public function send(Request $request){
        $validator = $this->validateContact($request);
        if($validator->fails()){
            return response()->json(['error'=>'Preencha todos os campos corretamente!'], $this->errorStatus);
        }
        $contact = array(
            'nome'=> $request->nome,
            'email'=> $request->email,
            'mensagem'=> $request->mensagem
        );
        Mail::to(env('CONTACT_TO'))->send(new ContactMail($contact));
        return response()->json(['success'=>'Mensagem enviada com sucesso!'], $this->successStatus);
    }

    private function validateContact($request){
        return Validator::make($request->all(), [ 
            'nome' => 'required|string',
            'email' => 'required|email',
            'mensagem' => 'required|string'
        ]);
    }
}
