<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Server Error</title>
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        .container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            color: #fa709a;
            margin: 0;
            line-height: 1;
        }
        h1 {
            font-size: 2rem;
            margin: 1rem 0;
            color: #1f2937;
        }
        p {
            color: #6b7280;
            margin: 1rem 0 2rem;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: #fa709a;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #e9608a;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">500</div>
        <h1>Server Error</h1>
        <p>Oops! Something went wrong on our end. We're working to fix it. Please try again later.</p>
        <a href="{{ url('/') }}" class="btn">Back to Home</a>
    </div>
</body>
</html>
