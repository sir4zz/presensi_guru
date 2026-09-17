<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;

class HistoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $month = request('month', now()->month);
        $year = request('year', now()->year);

        $history = $user->attendances()
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->latest('tanggal')
            ->get();

        return view('guru.history.index', compact('history'));
    }
}
