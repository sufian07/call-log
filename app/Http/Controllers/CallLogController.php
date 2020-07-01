<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Call;

class CallLogController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('start_date');
        $to = $request->input('end_date');
        $query =  Call::orderBy('id');
        if($request->input('status') != 'all') {
            $query->where('status', $request->input('status'));
        }
        $query->whereBetween('call_date', [$from, $to]);
        return $query->get();
    }
}
