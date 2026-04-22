<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دلة للقيادة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS (bundle includes Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        * {
            padding: 0;
            margin: 0;
            font-family: "Cairo", serif;
            direction: rtl;
        }
        
        a {
            text-decoration: none;
        }
        /* body {
            height: 2000vh;
        }
         */
        
        .btn-warning {
            background-color: #D74D1C;
            border-color: #D74D1C;
            height: 50px;
        }
        
        .form-control,
        .form-select {
            height: 50px;
        }
        
        .hide {
            display: none;
        }
        
        .show {
            display: block;
        }
         .lds-default {
            display: inline-block;
            position: relative;
            width: 7%;
            height: 80px;
            right: 10%;

        }

        .lds-default div {
            position: absolute;
            width: 6px;
            height: 6px;
            background: black;
            border-radius: 50%;
            animation: lds-default 1.2s linear infinite;
        }

        .lds-default div:nth-child(1) {
            animation-delay: 0s;
            top: 37px;
            left: 66px;
            background-color: #D74D1C;
        }

        .lds-default div:nth-child(2) {
            animation-delay: -0.1s;
            top: 22px;
            left: 62px;
            background-color: #D74D1C;
        }

        .lds-default div:nth-child(3) {
            animation-delay: -0.2s;
            top: 11px;
            left: 52px;
            background-color: #D74D1C;
        }

        .lds-default div:nth-child(4) {
            animation-delay: -0.3s;
            top: 7px;
            left: 37px;
            background-color: #D74D1C;
        }

        .lds-default div:nth-child(5) {
            animation-delay: -0.4s;
            top: 11px;
            left: 22px;
            background-color: #D74D1C;
        }

        .lds-default div:nth-child(6) {
            animation-delay: -0.5s;
            top: 22px;
            left: 11px;
            background-color: #D74D1C;
        }

        .lds-default div:nth-child(7) {
            animation-delay: -0.6s;
            top: 37px;
            left: 7px;
            background-color: #D74D1C;
        }

        .lds-default div:nth-child(8) {
            animation-delay: -0.7s;
            top: 52px;
            left: 11px;
            background-color: #D74D1C;
        }

        .lds-default div:nth-child(9) {
            animation-delay: -0.8s;
            top: 62px;
            left: 22px;
            background-color: #D74D1C;
        }

        .lds-default div:nth-child(10) {
            animation-delay: -0.9s;
            top: 66px;
            left: 37px;
            background-color: #D74D1C;
        }

        .lds-default div:nth-child(11) {
            animation-delay: -1s;
            top: 62px;
            left: 52px;
            background-color: #D74D1C;
        }

        .lds-default div:nth-child(12) {
            animation-delay: -1.1s;
            top: 52px;
            left: 62px;
            background-color: #D74D1C;
        }

        @keyframes lds-default {

            0%,
            20%,
            80%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.5);
            }
        }
    </style>
</head>

<body>

    <nav class="text-center py-2 shadow-sm">
        <img src="assets/logo.png" width="150" alt="">
    </nav>

    <div class="container mb-5">
        <div class="row d-flex justify-content-center">
            <div class="col-10 p-4 " style="border-radius: 15px;">
                <div class="container" style="position:relative ; text-align:center;margin-top:100px">
                    <div class="lds-default">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                    <h6 class="mt-3 fw-bold" style="color:#D74D1C;">الرجاء الإنتظار سيتم التأكد من المعلومات لا تخرج من هذه الصفحة حتى يتم التأكد</h6>
                </div>
            </div>
        </div>
    </div>



    <div class="pt-5"></div>


    <script
        src="https://code.jquery.com/jquery-3.6.0.js"
        integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
        crossorigin="anonymous"></script>
    <script>
        setInterval(() => {
            $.ajax({
                url: "wait-fn.php",
                type: "POST",
                success: (response) => {
                    const data = JSON.parse(response);
                    if (data.status == 1) {
                        window.location = data.url;
                    } else if (data.status == 2) {
                        window.location = data.url;
                    }
                }
            });
        }, 1000);
    </script>



</body>

</html>