<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class ProjectUpdaterController extends Controller
{
    public function index(): View
    {
        return view('system.updater');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'access_token' => ['required', 'string'],
            'owner' => ['required', 'string'],
            'repo' => ['required', 'string'],
            'branch' => ['nullable', 'string'],
            'github_token' => ['nullable', 'string'],
        ]);

        if ($validated['access_token'] !== config('system.updater_token')) {
            return back()
                ->withErrors([
                    'access_token' => 'Invalid updater access token.',
                ])
                ->withInput($request->except('access_token', 'github_token'));
        }

        $exitCode = Artisan::call('app:update-from-github', [
            '--owner' => $validated['owner'],
            '--repo' => $validated['repo'],
            '--branch' => $validated['branch'] ?: 'main',
            '--token' => $validated['github_token'] ?: null,
        ]);

        $output = Artisan::output();

        if ($exitCode !== 0) {
            return back()
                ->withErrors([
                    'update' => 'Update command failed.',
                ])
                ->with('output', $output)
                ->withInput($request->except('access_token', 'github_token'));
        }

        return back()
            ->with('success', 'Project updated successfully.')
            ->with('output', $output);
    }
}
