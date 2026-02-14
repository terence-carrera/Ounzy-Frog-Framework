<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Error <?= htmlspecialchars($status) ?></title>
    <style>
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            margin: 0;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            background: #111;
            color: #eee
        }

        .wrap {
            text-align: center;
            max-width: 520px;
            padding: 40px
        }

        h1 {
            font-size: 60px;
            margin: 0 0 10px;
            letter-spacing: 4px;
            color: #ff5252
        }

        h2 {
            margin: 0 0 25px;
            font-weight: 400;
            color: #ccc
        }

        p {
            line-height: 1.5;
            color: #aaa
        }

        a {
            color: #4fc3f7;
            text-decoration: none
        }

        a:hover {
            text-decoration: underline
        }

        footer {
            margin-top: 40px;
            font-size: 12px;
            color: #555
        }
    </style>
</head>

<body>
    <div class="wrap">
        <h1><?= htmlspecialchars($status) ?></h1>
        <h2><?= htmlspecialchars($message ?? 'An error occurred.') ?></h2>
        <p><?= htmlspecialchars($description ?? 'Please try again later.') ?></p>
        <p><a href="/">Return Home</a></p>
        <footer>Copyright &copy; <?= date('Y') ?> Frog Framework</footer>
    </div>
</body>

</html>