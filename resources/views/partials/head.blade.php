<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-6WPTZKW183"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-6WPTZKW183');
</script>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="theme-color" content="#000000" />
<meta name="description" content="Tailor management application" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="{{asset('favicon.ico')}}" sizes="any">
<link rel="icon" href="{{asset('favicon.svg')}}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{asset('apple-touch-icon.png')}}">
<link rel="manifest" href="{{asset('manifest.json')}}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- VAPID Public Key for Push Notifications -->
<meta name="vapid-public-key" content="{{ config('services.push_notifications.public_key') }}">
<!-- Push Notifications Script -->
<script src="{{ asset('js/push-notifications.js') }}"></script>
<!-- Auto Request Push Notification Permission -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add a one-time event listener for user interaction
        function handleUserInteraction() {
            // Check if already subscribed
            if (window.pushNotificationManager && window.pushNotificationManager.swRegistration) {
                window.pushNotificationManager.checkSubscription()
                    .then(isSubscribed => {
                        if (!isSubscribed) {
                            window.pushNotificationManager.subscribe()
                                .then(() => {
                                    console.log('Successfully subscribed to push notifications after user interaction');
                                })
                                .catch(error => {
                                    console.warn('Failed to subscribe to push notifications after user interaction:', error);
                                });
                        }
                    });
            }

            // Remove all event listeners after first interaction
            document.removeEventListener('click', handleUserInteraction);
            document.removeEventListener('keydown', handleUserInteraction);
            document.removeEventListener('scroll', handleUserInteraction);
            document.removeEventListener('touchstart', handleUserInteraction);
        }

        // Add event listeners for common user interactions
        document.addEventListener('click', handleUserInteraction);
        document.addEventListener('keydown', handleUserInteraction);
        document.addEventListener('scroll', handleUserInteraction);
        document.addEventListener('touchstart', handleUserInteraction);
    });
</script>

<!-- Simple Select CSS and JS -->
<link href="{{ asset('css/simple-select.css') }}" rel="stylesheet">
<script src="{{ asset('js/simple-select.js') }}"></script>

<!-- Intro.js for Gamified Tour -->
<link href="https://cdn.jsdelivr.net/npm/intro.js@7.2.0/minified/introjs.min.css" rel="stylesheet">
<link href="{{ asset('css/tour.css') }}" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/intro.js@7.2.0/minified/intro.min.js"></script>

@if(auth()->user()->onboarding_completed)
<script src="{{ asset('js/tour.js') }}"></script>
@endif

@vite(['resources/css/app.css', 'resources/js/app.js'])

@livewireStyles
@livewireScripts
@fluxAppearance
<script>
    // Check if the user needs to take the tour
    @auth
        var showTour = {{ auth()->user()->needsTour() ? 'true' : 'false' }};
    @else
        var showTour = false;
    @endauth

    // Reinitialize Select2 after Livewire updates

    Livewire.on('alert', (success) => {
        Swal.fire({
            icon: success[0].status,
            text: success[0].message
        })

    })
</script>

{{--<script type="text/javascript">--}}

{{--    (function(){--}}
{{--       tawk()--}}
{{--    }())--}}

{{--    function tawk(){--}}

{{--        setTimeout(function (){--}}
{{--            var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();--}}
{{--            var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];--}}
{{--            s1.async=true;--}}
{{--            s1.src='https://embed.tawk.to/686e122c6bd052190d8f071a/1ivmvdvnj';--}}
{{--            s1.charset='UTF-8';--}}
{{--            s1.setAttribute('crossorigin','*');--}}
{{--            s0.parentNode.insertBefore(s1,s0);--}}
{{--        },2000);--}}


{{--    }--}}
{{--</script>--}}
