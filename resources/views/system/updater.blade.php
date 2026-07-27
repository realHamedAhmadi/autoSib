@extends('layouts.app')

@section('title', 'Project Updater')

@section('content')
    <main>
        <header>
            <h1>GitHub Project Updater</h1>
            <p>Use this form to download the latest project archive from GitHub and update the current Laravel project.</p>
        </header>

        @if (session('success'))
            <section class="alert alert-success" aria-label="Success message">
                <p>{{ session('success') === true ? 'Update command executed successfully.' : session('success') }}</p>
            </section>
        @endif

        @if ($errors->any())
            <section class="alert alert-error" aria-labelledby="validation-errors-title">
                <h2 id="validation-errors-title">Validation errors</h2>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <form method="POST" action="{{ route('system.updater.update') }}">
            @csrf

            <section aria-labelledby="access-section-title">
                <fieldset>
                    <legend id="access-section-title">Access Settings</legend>

                    <div class="form-group">
                        <label for="access_token">Updater Access Token</label>
                        <input
                            type="password"
                            id="access_token"
                            name="access_token"
                            value="{{ old('access_token') }}"
                            autocomplete="off"
                            required
                        >
                        <p class="help-text">This value must match <code>APP_UPDATER_TOKEN</code> in the environment file.</p>
                    </div>
                </fieldset>
            </section>

            <section aria-labelledby="repository-section-title">
                <fieldset>
                    <legend id="repository-section-title">Repository Settings</legend>

                    <div class="form-group">
                        <label for="owner">GitHub Owner</label>
                        <input
                            type="text"
                            id="owner"
                            name="owner"
                            value="{{ old('owner', 'realHamedAhmadi') }}"
                            required
                        >
                        @error('owner')
                        <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="repo">Repository Name</label>
                        <input
                            type="text"
                            id="repo"
                            name="repo"
                            value="{{ old('repo', 'autoSib') }}"
                            required
                        >
                        @error('repo')
                        <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="branch">Branch</label>
                        <input
                            type="text"
                            id="branch"
                            name="branch"
                            value="{{ old('branch', 'main') }}"
                        >
                        @error('branch')
                        <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="github_token">GitHub Token</label>
                        <input
                            type="password"
                            id="github_token"
                            name="github_token"
                            value="{{ old('github_token') }}"
                            autocomplete="off"
                        >
                        <p class="help-text">Only required for private repositories.</p>
                        @error('github_token')
                        <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>
                </fieldset>
            </section>

            <section>
                <button type="submit">Run Update</button>
            </section>
        </form>

        @if (session('output'))
            <section aria-labelledby="output-title">
                <h2 id="output-title">Command Output</h2>
                <pre>{{ session('output') }}</pre>
            </section>
        @endif
    </main>
@endsection
