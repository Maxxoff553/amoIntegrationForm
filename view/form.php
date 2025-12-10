<?php
require_once '../crmLogic/logic.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../src/css/bootstrap.min.css">
    <title>Форма обратной связи</title>
</head>
<body>
    <br>
    <div class="container">
        <div class="row">
            <div class="col-md-4"> </div>
            <div class="col-md-4">
                <h4>Форма обратной связи</h4>
                <form action="" method="post">
                    <div class="form-group">
                        <label for="name">Ваше имя:</label>
                        <input type="text" name="contact_name" class="form-control" id="name" placeholder="Имя" required>
                    </div>
                    <div class="form-group">
                        <label for="email1">E-mail:</label>
                        <input type="email" name="contact_email" class="form-control" id="email1" placeholder="E-mail" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Номер телефона:</label>
                        <input type="tel" name="contact_phone" class="form-control" id="phone" placeholder="Номер телефона"
                               pattern="^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{10,11}$" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Цена:</label>
                        <input type="number" name="lead_price" class="form-control" id="phone" placeholder="Цена" required>
                    </div>
                    <input type="hidden" name="timer_start" value="<?=$timerStart?>">
                    <BR>
                    <button type="submit" class="btn btn-info">Отправить сообщение</button>
                </form>
            </div>
            <div class="col-md-4"> </div> </div>
    </div>
</body>
</html>