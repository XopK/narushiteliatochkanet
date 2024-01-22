<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function signin()
    {
        return view('signin');
    }

    public function signup()
    {
        return view('signup');
    }

    public function signin_valid(Request $request)
    {
        $request->validate([
            "login" => "required",
            "password" => "required",
        ], [
            "login.required" => "Поле обязательно для заполнения!",
            "password.required" => "Поле обязательно для заполнения!",
        ]);

        $user_authorization = $request->only("login", "password");

        if (Auth::attempt(["login" => $user_authorization['login'], "password" => $user_authorization['password']])) {
            if (Auth::user()->role == 1) {
                return redirect('/admin')->with('success', 'Вы вошли как Администратор');
            }
        } elseif (Auth::user()->role == 2) {
            return redirect('/personal-data')->with('success', 'Добро пожаловать');
        } else {
            return redirect('/signin')->with('error', 'Ошибка авторизации');
        }
    }

    public function sign_out()
    {
        Session::flush();
        Auth::logout();
        return redirect('/');
    }

    public function signup_valid(Request $request)
    {

        $request->validate([
            "name" => "alpha_dash|required|regex:/[А-Яа-яЁё]/u",
            "surname" => "alpha_dash|required|regex:/[А-Яа-яЁё]/u",
            "patronymic" => "alpha_dash|required|regex:/[А-Яа-яЁё]/u",
            "email" => "required|unique:users|email",
            "login" => "required|unique:users",
            "phone" => "required|regex:/\+7\([0-9][0-9][0-9]\)[0-9]{3}(\-)[0-9]{2}(\-)[0-9]{2}$/",
            "password" => "required|min:6",
        ], [
            "email.required" => "Поле обязательно для заполнения!",
            "email.email" => "Введите корректный email",
            "email.unique" => "Данный email уже занят",
            "name.required" => "Поле обязательно для заполнения!",
            "name.alpha_dash" => "Имя должно состоять только из букв!",
            "name.regex" => "Только кириллица",
            "surname.required" => "Поле обязательно для заполнения!",
            "surname.alpha_dash" => "Фамилия должно состоять только из букв!",
            "surname.regex" => "Только кириллица",
            "patronymic.required" => "Поле обязательно для заполнения!",
            "patronymic.alpha_dash" => "Отчество должно состоять только из букв!",
            "patronymic.regex" => "Только кириллица",
            "login.required" => "Поле обязательно для заполнения!",
            "phone.required" => "Поле обязательно для заполнения!",
            "phone.numeric" => "Номер только из цифр!",
            "phone.regex" => "Неправильный формат номера",
            "password.required" => "Поле обязательно для заполнения!",
        ]);

        $user = $request->all();

        $userCreate = User::create([
            'name' => $user['name'],
            'surname' => $user['surname'],
            'patronymic' => $user['patronymic'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'login' => $user['login'],
            'password' => Hash::make($user['password']),
            'role' => 2,
        ]);

        if ($userCreate) {
            Auth::login($userCreate);
            return redirect("/")->with("success", "Вы зарегистрировались!");
        } else {
            return redirect()->back()->with("error", "Ошибка регистрации!");
        }
    }
}
