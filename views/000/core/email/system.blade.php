<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 08.02.2017
 * Time: 15:51
 */
?>
@extends('core::001.email.layout.main')

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
