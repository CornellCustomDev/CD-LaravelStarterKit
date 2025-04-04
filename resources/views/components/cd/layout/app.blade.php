<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title }}</title>

    <link rel="icon" href="{{ asset('cwd-framework/favicon.ico') }}" type="image/vnd.microsoft.icon"/>

    <link href="{{ asset('cwd-framework/css/base.css') }}" rel="stylesheet">
    <link href="{{ asset('cwd-framework/css/cornell.css') }}" rel="stylesheet">
    <link href="{{ asset('cwd-framework/css/cwd_utilities.css') }}" rel="stylesheet">

    <!-- Activate for Cornell.edu typography and basic patterns -->
    <!-- <link rel="stylesheet" href="https://use.typekit.net/nwp2wku.css"> -->
    <!-- <link href="{{ asset('cwd-framework/css/cwd_patterns.css') }}" rel="stylesheet"> -->

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Icon Fonts -->
    <link href="{{ asset('cwd-framework/fonts/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('cwd-framework/fonts/material-design-iconic-font.min.css') }}" rel="stylesheet">

    @livewireStyles
</head>
<body class="cu-seal sidebar sidebar-right sidebar-tint sidebar-tint-edge">

<div id="skipnav"><a href="#main">Skip to main content</a></div>

<div class="band" id="super-header">
    <x-cd.layout.cu-header :title="$title" :subtitle="$subtitle"/>
    <x-cd.layout.site-header/>
</div>

<div id="main-content" class="band">
    <main id="main" class="container-fluid aria-target" tabindex="-1">
        <div class="row">
            <x-cd.layout.sidebar-top/>
            <x-cd.layout.main-article>
                {{ $slot }}
            </x-cd.layout.main-article>
            <x-cd.layout.sidebar-bottom/>
        </div>
    </main>
</div>

<x-cd.layout.supplementary-content/>

<x-cd.layout.footer/>

<!-- jQuery and Contributed Components -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

<!-- CWD Components -->
<script src="{{ asset('cwd-framework/js/cwd.js') }}"></script>
<script src="{{ asset('cwd-framework/js/cwd_utilities.js') }}"></script>

@livewireScripts
</body>
</html>
