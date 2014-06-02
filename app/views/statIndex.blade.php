<?php
/**
 * $from string
 * $to string
 * $idString string
 * $publics array
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sign in &middot; Twitter Bootstrap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    {{ HTML::style('assets/css/bootstrap.css') }}

    <!--[if lt IE 9]>
    <script src="../assets/js/html5shiv.js"></script>
    <![endif]-->

</head>

<body>
<div class="container">
    <?php
        if ( $errorMsg ) {?>
            <h3 style="color: red;" class="form-signin-heading"><?=$errorMsg?></h3>
        <?php }
    ?>
    <form class="form" method="POST" action="" width="300px">
        <h3 class="form-signin-heading">Что считать будем?</h3>


        <br>
        <label for="name">Ссылка на источник</label>
        <input type="text" name="idString" class="form-control"  value="{{ $idString }}">
        <br>
        <br>
        <input class="btn btn-large btn-primary" type="submit" value="Добавить паблик" >
    </form>

    <?php if (!is_null($resultIds)) { ?>
        <table>
            <thead>
                <tr><th>Юзеры</th></tr>
            </thead>
            <tbody>
                <?php foreach( $resultIds as $id) { ?>
                    <tr><td><?= $id ?></td></tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

    <?php if ( !is_null($publics)) {  ?>
    <table сlass="table">
        <thead>
        <tr><td>Паблик</td></td></tr>
        </thead>
        <tbody>
        <?php  foreach( $publics as $public) { ?>
            <tr><td><?= $public->public_id ?></td></tr>
    <?php } ?>
        </tbody>
    </table>
<?php } ?>
</div>


</body>
</html>
