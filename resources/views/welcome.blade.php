<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mazer Admin Dashboard</title>

    <link rel="stylesheet" href="{{ asset('template/assets/css/main/app.css') }}">
    <link rel="stylesheet" href="{{ asset('template/assets/css/main/app-dark.css') }}">
    <link rel="shortcut icon" href="{{ asset('template/assets/images/logo/favicon.svg') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('template/assets/images/logo/favicon.png') }}" type="image/png">

    <link rel="stylesheet" href="{{ asset('template/assets/css/shared/iconly.css') }}">


</head>

<body>

    <body style="padding: 5% 10%;">

        <div>
            <div class="card o-hidden border-0 shadow-lg my-5 col-lg-4 mx-auto">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Sign in</h1>
                                </div>
                                <form action="http://127.0.0.1:8000/" method="post">
                                    <div class="form-group">
                                        <label for="username">Username</label>
                                        <input class="form-control" type="text" name="username" id="username" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input class="form-control" type="password" name="password" id="password" required>
                                    </div>
                                    <button class="btn btn-primary btn-user btn-block" type="submit" name="login">Login</button>
                                </form>
                                <hr>
                                <div class="text-center">
                                    <p class="small">Lupa Password? <a href="http://127.0.0.1:8000/">Hubungi admin</a></p>
                                </div>
                                <hr>
                                <div class="text-center">
                                    <p class="small">Punya Kendala? <a href="http://127.0.0.1:8000/">Customer Service</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </body>

</html>