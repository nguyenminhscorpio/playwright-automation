<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deck;
use App\Models\ImportJob;
use App\Models\User;
use App\Services\Import\TxtImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ImportController extends Controller
{
    public function __construct(
        private readonly TxtImportService $txtImportService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);

        return response()->json([
            'items' => $this->txtImportService->listJobs($user),
        ]);
    }

    public function show(Request $request, ImportJob $importJob): JsonResponse
    {
        $user = $this->resolveUser($request);

        return response()->json($this->txtImportService->showJob($user, $importJob));
    }

    public function rows(Request $request, ImportJob $importJob): JsonResponse
    {
        $user = $this->resolveUser($request);

        return response()->json([
            'items' => $this->txtImportService->listRows($user, $importJob),
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'deck_id' => ['required', 'integer', 'exists:decks,id'],
            'file' => ['required', 'file', 'extensions:txt'],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);
        $deck = Deck::query()->findOrFail($validated['deck_id']);

        return response()->json(
            $this->txtImportService->preview($user, $deck, $request->file('file'))
        );
    }

    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'import_job_id' => ['required', 'integer', 'exists:import_jobs,id'],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);
        $importJob = ImportJob::query()->findOrFail($validated['import_job_id']);

        return response()->json(
            $this->txtImportService->confirm($user, $importJob)
        );
    }

    private function resolveUser(Request $request): User
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        return User::query()->findOrFail($validated['user_id']);
    }
}
