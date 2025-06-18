<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{{ config('app.name') }} - Offline</title>
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <style>
            body {
                font-family: 'Instrument Sans', sans-serif;
                background-color: #f8fafc;
                color: #1e293b;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 1rem;
                text-align: center;
            }
            .dark body {
                background-color: #1e1e1e;
                color: #f8fafc;
            }
            .container {
                max-width: 600px;
                padding: 2rem;
                background-color: white;
                border-radius: 0.5rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            .dark .container {
                background-color: #2d3748;
            }
            h1 {
                font-size: 1.5rem;
                font-weight: 600;
                margin-bottom: 1rem;
            }
            p {
                margin-bottom: 1.5rem;
            }
            .icon {
                font-size: 3rem;
                margin-bottom: 1rem;
            }
            .btn {
                display: inline-block;
                background-color: #4f46e5;
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 0.25rem;
                text-decoration: none;
                font-weight: 500;
                transition: background-color 0.2s;
            }
            .btn:hover {
                background-color: #4338ca;
            }
            .logo {
                margin-bottom: 2rem;
                width: 80px;
                height: 80px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 42">
                    <path
                        fill="currentColor"
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3Zm-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z"
                    />
                </svg>
            </div>
            <h1>You're currently offline</h1>
            <p>It seems you don't have an internet connection right now. Some features may be unavailable until you're back online.</p>
            <p>Don't worry, any data you submit will be saved and synchronized when your connection is restored.</p>
            <a href="/" class="btn">Try again</a>
        </div>
        <script>
            // Check if we're back online
            window.addEventListener('online', () => {
                window.location.reload();
            });
        </script>
    </body>
</html>
