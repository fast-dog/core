@extends('vendor.fast_dog.000.core.email.layout.main')

@section('title')
    <?=$title?>
@stop

@section('content')
    <h4><?=$title_header?></h4>
@stop
@section('content2')
    <?=$content ?>
@stop
@section('content3')
@stop
@section('content4')
    <p>Письмо сформировано автоматически, не нужно на него отвечать.</p>
@stop
