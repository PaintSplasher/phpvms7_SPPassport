@extends('sppassport::layouts.admin')

@section('title', 'SPPassport')
@section('actions')

@endsection
@section('content')
    <div class="card border-blue-bottom">
        <div class="header"><h4 class="title">Admin Scaffold!</h4></div>
        <div class="content">
            <p>This view is loaded from module: {{ config('sppassport.name') }}</p>
        </div>
    </div>
@endsection
