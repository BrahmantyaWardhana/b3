<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Inline script to detect system dark mode preference and apply it immediately --}}
  <script>
    (function () {
      const appearance = '{{ $appearance ?? "system" }}';

      if (appearance === 'system') {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (prefersDark) {
          document.documentElement.classList.add('dark');
        }
      }
    })();
  </script>

  {{-- Inline style to set the HTML background color based on our theme in app.css --}}
  <style>
    html {
      background-color: oklch(1 0 0);
    }

    html.dark {
      background-color: oklch(0.145 0 0);
    }
  </style>

  <title inertia>{{ config('app.name', 'Laravel') }}</title>

  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">

  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

  <script>
    (() => {
      // ==================== CONFIG ====================
      const SITE_KEY = 'SITE_PUBLIC_KEY'; // your public site id
      const STORE_URL = '127.0.0.1:3000/api/storeTrafficData';
      // ==================== CONFIG ====================

      let lastSentUrl = '';
      let prevUrl = document.referrer || null;

      function send(site_key, currentUrl, ref) {
        const payload = { site_key };
        if (currentUrl) payload.current_url = currentUrl;
        if (ref) payload.ref = ref;
        const bodyStr = JSON.stringify(payload);
        const blob = new Blob([bodyStr], { type: 'application/json' });

        console.log('[network-script] Sending traffic payload:', payload);

        if (!navigator.sendBeacon || !navigator.sendBeacon(STORE_URL, blob)) {
          fetch(STORE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: bodyStr,
            keepalive: true
          }).catch(() => { });
        }
      }

      function sendIfNewUrl() {
        const curr = window.location.href;
        if (curr === lastSentUrl) return;
        send(SITE_KEY, curr, prevUrl);
        lastSentUrl = curr;
        prevUrl = curr;
      }

      sendIfNewUrl();

      const _push = history.pushState;
      const _replace = history.replaceState;
      history.pushState = function () {
        _push.apply(this, arguments);
        queueMicrotask(() => sendIfNewUrl());
      };
      history.replaceState = function () {
        _replace.apply(this, arguments);
        queueMicrotask(() => sendIfNewUrl());
      };
      window.addEventListener('popstate', sendIfNewUrl);
      window.addEventListener('hashchange', sendIfNewUrl);
    })();
  </script>



  @viteReactRefresh
  @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
  @inertiaHead
</head>

<body class="font-sans antialiased">
  @inertia
</body>

</html>