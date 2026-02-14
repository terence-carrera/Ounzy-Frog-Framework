<?php // Default landing page view for Frog Framework using landing layout 
?>
@extends('Layout.landing')

@section('hero')
<img src="<?= asset('images/logo.png') ?>" alt="Frog Logo" class="landing-logo" />
<h2>Build faster with a framework you can read in one sitting.</h2>
<p>Frog gives you routing, middleware, simple dependency injection, a Blade-like mini template system, and asset helpers in a few tiny files. Stay productive without the weight.</p>
<div class="landing-cta">
    <a class="landing-btn" href="https://github.com" target="_blank" rel="noopener">View Source</a>
    <a class="landing-btn secondary" href="/docs" rel="noopener">Quick Docs</a>
</div>
@endsection

@section('content')
<p class="landing-content">Drop this into a small project, experiment, and grow it only when you need to. The code is intentionally compact so you can understand and extend it rapidly.</p>
@endsection

@section('features')
<div class="landing-feature">
    <h3>Routing</h3>
    <p>Named routes, groups, middleware pipeline, and simple params.</p>
</div>
<div class="landing-feature">
    <h3>Templating</h3>
    <p>Sections, layouts, stacks, and includes with a tiny compiler.</p>
</div>
<div class="landing-feature">
    <h3>CLI Tools</h3>
    <p>Scaffold controllers & views, manage assets, list routes.</p>
</div>
<div class="landing-feature">
    <h3>Assets</h3>
    <p>Symlink or copy resources with cache-busted URLs.</p>
</div>
<div class="landing-feature">
    <h3>Readable Core</h3>
    <p>Skim the framework in minutes. Modify to fit your needs.</p>
</div>
<div class="landing-feature">
    <h3>No Bloat</h3>
    <p>Focused features only; keep control of your stack.</p>
</div>
@endsection

@section('footer')
<div>Released under MIT. Customize freely.</div>
@endsection