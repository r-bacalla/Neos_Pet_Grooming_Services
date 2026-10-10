<?php

namespace App\Http\Controllers;

use App\Models\GroomingAppointment;
use Illuminate\Http\Request;

class GroomingAppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $appointments = \App\Models\GroomingAppointment::with([
        'customer',
        'pet',
        'service',
        'groomer',
    ])
    ->orderBy('scheduled_start')
    ->get();

    return view('grooming.index', compact('appointments'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(GroomingAppointment $groomingAppointment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GroomingAppointment $groomingAppointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GroomingAppointment $groomingAppointment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GroomingAppointment $groomingAppointment)
    {
        //
    }
}
