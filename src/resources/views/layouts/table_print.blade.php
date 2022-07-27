@extends('4myth-tools::layouts.print')

@php
    $headerSpace = ( $headerSpace ?? 150);
    $footerSpace = ( $footerSpace ?? 50);
@endphp

@push('styles')
    <style>
        body {
            font-size: 1rem;
        }

        .header-space {
            height: {!! $headerSpace !!}px;
        }

        .footer-space {
            height: {!! $footerSpace !!}px;
        }

        .header,
        .footer {
            background-color: white;
            position: fixed;
            left: 0;
            width: 100%;
        }

        .header {
            top: 1px;
        }

        .footer {
            bottom: 0;
        }

        .main-width,
        .header,
        .footer {
            width: 100%;
        }

        html, body {
            min-height: 384mm;
        }

    </style>
@endpush
@section('print_content')
    <table class="main-width">
        <thead>
        <tr>
            <td>
                <div class="header-space"></div>
            </td>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                @yield('table_content')
            </td>
        </tr>
        </tbody>
        <tfoot>
        <tr>
            <td>
                <div class="footer-space"></div>
            </td>
        </tr>
        </tfoot>
    </table>
    <div class="header">
        @yield('table_header')
    </div>
    <div class="footer">
        @yield('table_footer')
    </div>
@stop
