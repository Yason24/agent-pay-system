<?php /** @var string $message */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>{{ $message }}</h1>
    <p><a href="/login">Open login</a></p>
</section>
@endsection