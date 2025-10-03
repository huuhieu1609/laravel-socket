<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatViewController extends Controller
{
    public function index()
    {
        // Trang chat sẽ được xử lý bởi Vue.js
        return view('chat');
    }
}
