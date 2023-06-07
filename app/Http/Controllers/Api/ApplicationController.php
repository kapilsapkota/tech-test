<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $planType = $request->query('planType');

        $applications = Application::query()
            ->join('customers as c', 'c.id', '=', 'applications.customer_id')
            ->join('plans as p', 'p.id', '=', 'applications.plan_id')
            ->select('applications.id as application_id',
                DB::raw("CONCAT(c.first_name,'',c.last_name) AS customer_full_name"),
                'applications.address_1 as address', 'p.type as plan_type', 'p.name as plan_name',
                'applications.state', 'p.monthly_cost as plan_monthly_cost',
                'applications.order_id', 'applications.status')
            ->when($planType, function ($query, $planType) {
                return $query->where('p.type', $planType);
            })
           ->oldest()
            ->paginate(10);

        // Format the plan monthly cost to human readable dollar format
        $applications->getCollection()->transform(function ($application) {
            $application->plan_monthly_cost = number_format($application->plan_monthly_cost / 100, 2);
            return $application;
        });

        // Include the order ID for applications with the complete status
        $applications->getCollection()->each(function ($application) {
            if ($application->status == 'complete') {
                $application->order_id = $application->order_id;
            } else {
                $application->order_id = null;
            }
        });

        return response()->json($applications);
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
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
