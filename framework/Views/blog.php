<h1>Blog View</h1>
<p>Generated scaffold.</p>
<p>Service example output: <?= htmlspecialchars(
                                app()->container()->make(Frog\App\Services\BlogService::class)->example(),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></p>