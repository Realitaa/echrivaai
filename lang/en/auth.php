<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    /*
    |--------------------------------------------------------------------------
    | Authentication Page 
    |--------------------------------------------------------------------------
    */

    "sign_in" => [
        "title" => "Sign in to your account",
        "subtitle" => "Enter your email and password below to sign in",
        "button" => "Sign in"
    ],

    "register" => [
        "title" => "Create an account",
        "subtitle" => "Enter your email and password below to register",
        "button" => "Register"
    ],

    "confirm_password" => [
        "title"=> "Confirm your password",
        "description" => "This is a secure area of the application. Please confirm your password before continuing.",
        "button" => "Confirm Password"
    ],

    "form"=> [
        "name" => "Name",
        "email" => "Email Address",
        "password" => "Password",
        "confirm_password" => "Confirm Password",
        "remember_me" => "Remember me",
        "sign_in_button" => "Sign in",
        "forgot_password" => "Forgot password?",
        "role" => "Role",
        "role_student" => "Student",
        "role_teacher" => "Teacher"
    ],

    "placeholder" => [
        "name" => "Full name"
    ],

    "forgot_password" => [
        "title" => "Forgot password",
        "description" => "Enter your email to receive a password reset link",
        "button" => "Email password reset link",
        "return_to_login" => "Or, return to",
    ],

    "reset_password" => [
        "title" => "Reset password",
        "description" => "Please enter your new password below",
        "button" => "Reset password",
    ],

    "links" => [
        "sign_up" => "Don't have an account?",
        "sign_up_link" => "Sign up",
        "sign_in" => "Already have an account?",
        "sign_in_link" => "Sign in"
    ]
];
