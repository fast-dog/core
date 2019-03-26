<?php

?>
@extends('core::layouts.admin.default')

@section('title')
    Авторизация
@endsection

@section('body_class')
    login
@endsection

@section('content')
    <div>
        <div class="login_wrapper">
            <div class="animate form login_form">
                <section class="login_content">
                    <form method="post" action="/admin/login">
                        {{ csrf_field() }}
                        <h1>Авторизация</h1>
                        <div>
                            <input type="text" class="form-control" name="email" placeholder="Username" required=""/>
                        </div>
                        <div>
                            <input type="password" class="form-control" name="password" placeholder="Password" required=""/>
                        </div>
                        <div>
                            <button class="btn btn-default submit" type="submit">вход</button>
                            {{--<a class="reset_pass" href="#">Lost your password?</a>--}}
                        </div>

                        <div class="clearfix"></div>

                        <div class="separator">
                            <div class="clearfix"></div>
                            <br/>
                            <div>
                                <h1><i class="fa fa-paw"></i> FastDog CMS 2.X</h1>
                            </div>
                        </div>
                    </form>
                </section>
            </div>

            <div id="register" class="animate form registration_form">
                <section class="login_content">
                    <form>
                        <h1>Create Account</h1>
                        <div>
                            <input type="text" class="form-control" placeholder="Username" required=""/>
                        </div>
                        <div>
                            <input type="email" class="form-control" placeholder="Email" required=""/>
                        </div>
                        <div>
                            <input type="password" class="form-control" placeholder="Password" required=""/>
                        </div>
                        <div>
                            <a class="btn btn-default submit" href="index.html">Submit</a>
                        </div>

                        <div class="clearfix"></div>

                        <div class="separator">
                            <p class="change_link">Already a member ?
                                <a href="#signin" class="to_register"> Log in </a>
                            </p>

                            <div class="clearfix"></div>
                            <br/>

                            <div>
                                <h1><i class="fa fa-paw"></i> Gentelella Alela!</h1>
                                <p>©2016 All Rights Reserved. Gentelella Alela! is a Bootstrap 3 template. Privacy and Terms</p>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
@endsection