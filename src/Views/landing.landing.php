<?php // Default landing page view for Frog Framework using landing layout ?>
@extends('Layout.landing')

@section('hero')
    <img src="<?= asset('images/logo.png') ?>" alt="Frog Logo" style="height:90px;width:auto;filter:drop-shadow(0 4px 14px rgba(0,0,0,.45));margin-bottom:1.2rem;" />
    <h2>Build faster with a framework you can read in one sitting.</h2>
    <p>Frog gives you routing, middleware, simple dependency injection, a Blade-like mini template system, and asset helpers in a few tiny files. Stay productive without the weight.</p>
    <div class="cta-row">
        <a class="btn" href="https://github.com" target="_blank" rel="noopener">View Source</a>
        <a class="btn secondary" href="/docs" rel="noopener">Quick Docs</a>
    </div>
@endsection

@section('content')
    <p style="max-width:680px;margin:0 auto 2.2rem;text-align:center;color:#94a3b8;">Drop this into a small project, experiment, and grow it only when you need to. The code is intentionally compact so you can understand and extend it rapidly.</p>
@endsection

@section('features')
    <div class="feature">
        <h3>Routing</h3>
        <p>Named routes, groups, middleware pipeline, and simple params.</p>
    </div>
    <div class="feature">
        <h3>Templating</h3>
        <p>Sections, layouts, stacks, and includes with a tiny compiler.</p>
    </div>
    <div class="feature">
        <h3>CLI Tools</h3>
        <p>Scaffold controllers & views, manage assets, list routes.</p>
    </div>
    <div class="feature">
        <h3>Assets</h3>
        <p>Symlink or copy resources with cache-busted URLs.</p>
    </div>
    <div class="feature">
        <h3>Readable Core</h3>
        <p>Skim the framework in minutes. Modify to fit your needs.</p>
    </div>
    <div class="feature">
        <h3>No Bloat</h3>
        <p>Focused features only; keep control of your stack.</p>
    </div>
@endsection

@section('footer')
    <div>Released under MIT. Customize freely.</div>
@endsection
