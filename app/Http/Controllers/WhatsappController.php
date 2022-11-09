<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;


use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    public function auth()
    {
        $req = Http::withBasicAuth('admin', '83e4060e-78e1-4fe5-9977-aeeccd46a2b8')
                        ->get('http://127.0.0.1:3000/api/v1/whatsapp/auth');

        return $req->json();
    }

    public function qrCode()
    {
        $req = Http::withHeaders([
            'Authorization'=>'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXQiOnsiamlkIjoiYWRtaW4ifSwiZXhwIjoxNjY2NTE4NTU2LCJpYXQiOjE2NjY0MzIxNTZ9.No6pqPPDqOp9YIOloAdok2xfuBRahLYfYcKBAXbDi70'
        ])
        ->post('http://127.0.0.1:3000/api/v1/whatsapp/login');

       return $req->body();
    }
}
