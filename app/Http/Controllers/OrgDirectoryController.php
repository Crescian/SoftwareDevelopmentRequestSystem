<?php

namespace App\Http\Controllers;

use App\Services\OrgDirectoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgDirectoryController extends Controller
{
    public function __construct(private readonly OrgDirectoryService $orgDirectory)
    {
    }

    public function businessUnits(): JsonResponse
    {
        return $this->respond(fn () => $this->orgDirectory->businessUnits());
    }

    public function companies(Request $request): JsonResponse
    {
        $request->validate(['business_unit_id' => ['required', 'uuid']]);

        return $this->respond(fn () => $this->orgDirectory->companies($request->string('business_unit_id')->toString()));
    }

    public function departments(Request $request): JsonResponse
    {
        $request->validate(['company_id' => ['required', 'uuid']]);

        return $this->respond(fn () => $this->orgDirectory->departments($request->string('company_id')->toString()));
    }

    private function respond(callable $fetch): JsonResponse
    {
        try {
            return response()->json($fetch()->values());
        } catch (\Illuminate\Http\Client\RequestException $e) {
            return response()->json([
                'message' => 'The organization directory is unavailable right now. Please try again shortly.',
            ], 502);
        }
    }
}
