<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function destroy()
    {
        return redirect()->back()->with('success', 'Message supprimé');

    }



}
