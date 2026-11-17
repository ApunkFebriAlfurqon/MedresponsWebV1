<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Program::query()->orderBy('id')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'deskripsi' => ['required', 'string'],
        ]);

        $program = Program::create($validated);

        return response()->json([
            'message' => 'Program berhasil dibuat.',
            'data' => $program,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Program $program): JsonResponse
    {
        return response()->json(['data' => $program]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Program $program): JsonResponse
    {
        $validated = $request->validate([
            'nama' => [$request->isMethod('put') ? 'required' : 'sometimes', 'string', 'max:100'],
            'deskripsi' => [$request->isMethod('put') ? 'required' : 'sometimes', 'string'],
        ]);

        $program->fill($validated)->save();

        return response()->json([
            'message' => 'Program berhasil diperbarui.',
            'data' => $program->refresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program): JsonResponse
    {
        $program->delete();

        return response()->json(['message' => 'Program berhasil dihapus.']);
    }
}
