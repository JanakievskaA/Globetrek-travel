@php
    $socials = [
        'Facebook' => ['https://www.facebook.com/', 'icon-facebook'],
        'LinkedIn' => ['https://www.linkedin.com/', 'icon-linked'],
        'X' => ['https://x.com/', 'icon-XLogo'],
        'Instagram' => ['https://www.instagram.com/', 'icon-instgram'],
    ];
@endphp

<footer class="footer">
    <div class="top-footer">
        <div class="container">
            <div class="content-top-footer">
                <div class="footer-logo">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('assets/images/logo/logo-white.svg') }}" alt="{{ config('app.name') }}">
                    </a>
                </div>
                <div class="footer-socials flex-center">
                    <span class="text-social">Follow us:</span>
                    <ul class="list-social flex-center">
                        @foreach ($socials as $name => [$url, $icon])
                            <li>
                                <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $name }}">
                                    <i class="icon {{ $icon }}"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="inner-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="widget-box">
                        <div class="widget-title mb-6">
                            <div class="text-white h4">Contact us</div>
                        </div>
                        <div class="widget-info">
                            <div class="widget-contact">
                                <p class="label">Toll-free call</p>
                                <a href="tel:+12295550109" class="text-white h4">(229) 555-0109</a>
                            </div>
                            <div class="widget-contact mt-4">
                                <p class="label">Email</p>
                                <a href="mailto:hello@globetrek.travel" class="text-white">hello@globetrek.travel</a>
                            </div>
                            <div class="widget-contact mt-4">
                                <p class="label">Office</p>
                                <p class="text-white">32 Rivington Street<br>London EC2A 3LX, United Kingdom</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="widget-box">
                        <div class="widget-title mb-6">
                            <div class="text-white h4">Popular destinations</div>
                        </div>
                        <div class="widget-tour">
                            <ul class="tour-list tf-grid-layout tf-col-2 gap-list">
                                @foreach ($navDestinations as $destination)
                                    <li>
                                        <a href="{{ route('destinations.show', $destination) }}">{{ $destination->name }}</a>
                                    </li>
                                @endforeach
                                <li><a href="{{ route('tours.index') }}">All tours</a></li>
                                <li><a href="{{ route('destinations.index') }}">All destinations</a></li>
                                <li><a href="{{ route('about') }}">About us</a></li>
                                <li><a href="{{ route('contact') }}">Contact</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="widget-box">
                        <div class="widget-title mb-6">
                            <div class="text-white h4">Newsletter</div>
                        </div>
                        <div class="widget-newseletter">
                            <p class="text-note">
                                Trip notes, new departures and the occasional flash fare. No more than twice a month.
                            </p>
                            <form class="footer-send mt-4" method="POST" action="{{ route('newsletter.store') }}">
                                @csrf
                                <input type="email" name="email" placeholder="Your email address" required>
                                <button type="submit" aria-label="Subscribe">
                                    <img src="{{ asset('assets/images/icons/icon-send.svg') }}" alt="Send">
                                </button>
                            </form>
                            @error('email')
                                <p class="gt-form-error mt-2">{{ $message }}</p>
                            @enderror
                            <div class="action-download mt-10 d-flex gap-3">
                                <a href="#"><img src="{{ asset('assets/images/logo/logo-gg.png') }}" alt="Get it on Google Play"></a>
                                <a href="#"><img src="{{ asset('assets/images/logo/logo-app.png') }}" alt="Download on the App Store"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bottom-footer">
        <div class="container">
            <div class="content-bottom">
                <div class="copy-right">
                    © {{ date('Y') }} <a href="{{ route('home') }}" class="fw-bold">GlobeTrek</a>. Demo travel platform built on the GlobeTrek template.
                </div>
                <ul class="menu-bottom flex-center">
                    <li><a href="{{ route('about') }}">About</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                    @if (auth()->user()?->isStaff())
                        <li><a href="/admin">Admin panel</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</footer>
